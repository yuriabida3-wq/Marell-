<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Payment;
use App\Models\Student;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BursarController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $todayTotal = Payment::where('status', 'completed')->whereDate('created_at', $today)->sum('amount');
        $todayCount = Payment::where('status', 'completed')->whereDate('created_at', $today)->count();
        $defaulters = Student::where('balance', '>', 0)->count();
        $totalOutstanding = Student::where('balance', '>', 0)->sum('balance');

        $recent = Payment::with('student')->where('status', 'completed')->latest()->take(5)->get();

        return view('bursar.dashboard', compact('todayTotal', 'todayCount', 'defaulters', 'totalOutstanding', 'recent'));
    }

    // ---------- FIND STUDENT ----------
    public function students(Request $request)
    {
        $student = null;

        if ($request->filled('adm')) {
            $student = Student::where('adm_no', trim($request->adm))->first();
            if (!$student && $request->wantsJson()) {
                return response()->json(['error' => 'Not found']);
            }
        }

        return view('bursar.students', compact('student'));
    }

    // ---------- RECORD PAYMENT (Cash/Bank) ----------
    public function recordForm(Request $request)
    {
        $student = null;
        if ($request->filled('adm')) {
            $student = Student::where('adm_no', trim($request->adm))->first();
        }
        return view('bursar.record', compact('student'));
    }

    public function record(Request $request)
    {
        $data = $request->validate([
            'adm'    => 'required|string',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:Cash,Bank,M-Pesa',
            'notes'  => 'nullable|string|max:500',
            'send_sms' => 'nullable|boolean',
        ]);

        $student = Student::where('adm_no', trim($data['adm']))->first();
        if (!$student) return back()->with('error', 'Student not found.')->withInput();

        $payment = null;

        DB::transaction(function () use ($student, $data, &$payment) {
            $locked = Student::where('id', $student->id)->lockForUpdate()->first();

            $receiptNo = Payment::generateReceiptNo();
            $txnCode   = $data['method'] === 'M-Pesa' ? null : strtoupper($data['method'] . '-' . uniqid());

            $payment = Payment::create([
                'student_id'       => $locked->id,
                'amount'           => $data['amount'],
                'method'           => $data['method'],
                'transaction_code' => $txnCode,
                'status'           => 'completed',
                'receipt_no'       => $receiptNo,
                'term'             => 'Term 1',
                'year'             => date('Y'),
                'recorded_by'      => auth()->id(),
                'notes'            => $data['notes'] ?? null,
            ]);

            $newPaid    = (float) $locked->paid_amount + (float) $data['amount'];
            $newBalance = max(0, (float) $locked->total_fee - $newPaid);

            $locked->update(['paid_amount' => $newPaid, 'balance' => $newBalance]);
        });

        // Optional SMS
        if (!empty($data['send_sms']) && $student->parent_phone) {
            try {
                $msg = sprintf(
                    "MARELL ACADEMY\nReceived KES %s\nReceipt: %s\nBalance: KES %s\nThank you.",
                    number_format($data['amount'], 2),
                    $payment->receipt_no,
                    number_format($student->fresh()->balance, 2)
                );
                app(SmsService::class)->send($student->parent_phone, $msg);
            } catch (\Throwable $e) {}
        }

        return redirect()->route('bursar.students', ['adm' => $student->adm_no])
            ->with('success', "Payment of KES " . number_format($data['amount']) . " recorded for {$student->name}. Receipt {$payment->receipt_no}.");
    }

    // ---------- RECEIPT PDF ----------
    public function receipt(Payment $payment)
    {
        if ($payment->status !== 'completed') {
            abort(404);
        }

        $student = $payment->student;
        $pdf = Pdf::loadView('pdf.receipt', compact('payment', 'student'))->setPaper('A4', 'portrait');
        return $pdf->download('Marell-Receipt-' . $payment->receipt_no . '.pdf');
    }

    // ---------- FEE STATEMENT PDF ----------
    public function statement(Student $student)
    {
        $payments = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();

        $pdf = Pdf::loadView('pdf.statement', compact('student', 'payments'))->setPaper('A4', 'portrait');
        return $pdf->download('Statement-' . $student->adm_no . '.pdf');
    }

    // ---------- SEND BALANCE SMS ----------
    public function sendBalance(Student $student)
    {
        if (!$student->parent_phone) {
            return back()->with('error', 'No parent phone on file.');
        }

        $msg = sprintf(
            "MARELL ACADEMY\nDear %s,\nFee balance for %s (%s) is KES %s.\nPay via M-Pesa: marell.ac.ke/pay\nThank you.",
            $student->parent_name,
            $student->name,
            $student->adm_no,
            number_format($student->balance, 2)
        );

        try {
            app(SmsService::class)->send($student->parent_phone, $msg);
            return back()->with('success', 'Balance SMS sent to ' . $student->parent_phone);
        } catch (\Throwable $e) {
            return back()->with('error', 'SMS failed: ' . $e->getMessage());
        }
    }

    // ---------- ALL PAYMENTS ----------
    public function payments(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $method = $request->get('method');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Payment::with('student')->where('status', 'completed');
        if ($q) {
            $query->where(function ($sq) use ($q) {
                $sq->where('transaction_code', 'like', "%{$q}%")
                   ->orWhere('receipt_no', 'like', "%{$q}%")
                   ->orWhereHas('student', function ($s) use ($q) {
                       $s->where('name', 'like', "%{$q}%")
                         ->orWhere('adm_no', 'like', "%{$q}%")
                         ->orWhere('parent_phone', 'like', "%{$q}%");
                   });
            });
        }
        if ($method) $query->where('method', $method);
        if ($from)   $query->whereDate('created_at', '>=', $from);
        if ($to)     $query->whereDate('created_at', '<=', $to);

        $payments = $query->latest()->paginate(30)->withQueryString();
        $total = (clone $query)->sum('amount');

        return view('bursar.payments', compact('payments', 'q', 'method', 'from', 'to', 'total'));
    }

    // ---------- FEE BALANCES (all classes) ----------
    public function balances(Request $request)
    {
        $class = $request->get('class');
        $onlyDefaulters = $request->boolean('defaulters');

        $query = Student::where('status', 'active');
        if ($class) $query->where('class', $class);
        if ($onlyDefaulters) $query->where('balance', '>', 0);

        $students = $query->orderBy('class')->orderBy('name')->paginate(40)->withQueryString();
        $classes  = Classroom::where('active', true)->orderBy('name')->pluck('name')->unique()->values();
        $totalOwed = (clone $query)->sum('balance');
        $totalCollected = (clone $query)->sum('paid_amount');

        return view('bursar.balances', compact('students', 'classes', 'class', 'onlyDefaulters', 'totalOwed', 'totalCollected'));
    }

    public function exportBalances(Request $request)
    {
        $class = $request->get('class');
        $onlyDefaulters = $request->boolean('defaulters');

        $query = Student::where('status', 'active');
        if ($class) $query->where('class', $class);
        if ($onlyDefaulters) $query->where('balance', '>', 0);

        $students = $query->orderBy('class')->orderBy('name')->get();
        $filename = 'Marell-Balances-' . ($class ? str_replace(' ', '-', $class) : 'All') . '-' . date('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($students) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['ADM','Name','Class','Parent','Phone','Total Fee','Paid','Balance']);
            foreach ($students as $s) {
                fputcsv($out, [
                    $s->adm_no, $s->name, $s->class, $s->parent_name, $s->parent_phone,
                    number_format((float)$s->total_fee, 2, '.', ''),
                    number_format((float)$s->paid_amount, 2, '.', ''),
                    number_format((float)$s->balance, 2, '.', ''),
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    // ---------- DAILY REPORT ----------
    public function daily(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $payments = Payment::with('student')
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->get();
        $total = $payments->sum('amount');
        $byMethod = $payments->groupBy('method')->map->sum('amount');

        return view('bursar.daily', compact('payments', 'date', 'total', 'byMethod'));
    }

    // ---------- BULK CSV UPLOAD ----------
    public function bulkForm()
    {
        return view('bursar.bulk');
    }

    public function bulkUpload(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt|max:2048']);

        $handle = fopen($request->file('csv')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $created = 0; $skipped = 0; $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 3) { $skipped++; continue; }

                $map = array_combine(array_map('strtolower', array_map('trim', $header)), array_map('trim', $row));
                $adm = $map['adm_no'] ?? $map['adm'] ?? null;
                $amount = $map['amount'] ?? null;
                $method = $map['method'] ?? 'Cash';

                if (!$adm || !$amount) { $skipped++; continue; }

                $student = Student::where('adm_no', $adm)->first();
                if (!$student) { $skipped++; $errors[] = "Student not found: {$adm}"; continue; }

                DB::transaction(function () use ($student, $amount, $method, &$created) {
                    $locked = Student::where('id', $student->id)->lockForUpdate()->first();
                    $receiptNo = Payment::generateReceiptNo();

                    Payment::create([
                        'student_id' => $locked->id,
                        'amount' => (float) $amount,
                        'method' => in_array($method, ['Cash','Bank','M-Pesa']) ? $method : 'Cash',
                        'status' => 'completed',
                        'receipt_no' => $receiptNo,
                        'term' => 'Term 1',
                        'year' => date('Y'),
                        'recorded_by' => auth()->id(),
                        'notes' => 'Bulk upload',
                    ]);

                    $newPaid = (float) $locked->paid_amount + (float) $amount;
                    $locked->update([
                        'paid_amount' => $newPaid,
                        'balance' => max(0, (float) $locked->total_fee - $newPaid),
                    ]);
                    $created++;
                });
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Bulk upload failed: ' . $e->getMessage());
        }
        fclose($handle);

        return redirect()->route('bursar.bulk')
            ->with('success', "Bulk upload done. Created: {$created}, Skipped: {$skipped}." . (count($errors) ? ' | ' . implode('; ', array_slice($errors, 0, 3)) : ''));
    }
}
