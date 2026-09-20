<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $method = $request->get('method');
        $status = $request->get('status');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Payment::with('student');

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
        if ($status) $query->where('status', $status);
        if ($from)   $query->whereDate('created_at', '>=', $from);
        if ($to)     $query->whereDate('created_at', '<=', $to);

        $payments = $query->latest()->paginate(30)->withQueryString();

        // Summary
        $sumQuery = clone $query;
        $totalSum = (clone $query)->where('status', 'completed')->sum('amount');
        $countSum = (clone $query)->count();

        return view('principal.finance.index', compact('payments', 'q', 'method', 'status', 'from', 'to', 'totalSum', 'countSum'));
    }

    public function recordForm(Request $request)
    {
        $student = null;
        if ($request->filled('adm')) {
            $student = Student::where('adm_no', trim($request->adm))->first();
        }
        return view('principal.finance.record', compact('student'));
    }

    public function record(Request $request)
    {
        $data = $request->validate([
            'adm'    => 'required|string',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:Cash,Bank,M-Pesa',
            'notes'  => 'nullable|string|max:500',
        ]);

        $student = Student::where('adm_no', trim($data['adm']))->first();
        if (!$student) return back()->with('error', 'Student not found.')->withInput();

        DB::transaction(function () use ($student, $data) {
            $locked = Student::where('id', $student->id)->lockForUpdate()->first();

            $receiptNo = Payment::generateReceiptNo();
            $txnCode   = $data['method'] === 'M-Pesa' ? null : strtoupper($data['method'] . '-' . uniqid());

            Payment::create([
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

            $locked->update([
                'paid_amount' => $newPaid,
                'balance'     => $newBalance,
            ]);
        });

        return redirect()->route('principal.finance.record', ['adm' => $student->adm_no])
            ->with('success', "Payment of KES " . number_format($data['amount']) . " recorded for {$student->name}.");
    }

    public function defaulters(Request $request)
    {
        $minBalance = (float) $request->get('min_balance', 1);
        $class      = $request->get('class');

        $query = Student::where('balance', '>=', $minBalance)->where('status', 'active');
        if ($class) $query->where('class', $class);

        $defaulters = $query->orderByDesc('balance')->paginate(40)->withQueryString();
        $classes    = Student::select('class')->distinct()->orderBy('class')->pluck('class');
        $totalOwed  = $query->sum('balance');

        return view('principal.finance.defaulters', compact('defaulters', 'classes', 'class', 'minBalance', 'totalOwed'));
    }

    public function exportPayments(Request $request)
    {
        $query = Payment::with('student')->latest();
        if ($request->filled('from'))   $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))     $query->whereDate('created_at', '<=', $request->to);
        if ($request->filled('method')) $query->where('method', $request->method);
        if ($request->filled('status')) $query->where('status', $request->status);

        $payments = $query->get();
        $filename = 'Marell-Payments-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payments) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Date', 'Receipt', 'Student', 'ADM', 'Class', 'Method', 'Txn Code', 'Amount', 'Status']);

            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->created_at->format('Y-m-d H:i'),
                    $p->receipt_no,
                    $p->student->name ?? '—',
                    $p->student->adm_no ?? '—',
                    $p->student->class ?? '—',
                    $p->method,
                    $p->transaction_code ?: '',
                    number_format((float) $p->amount, 2, '.', ''),
                    $p->status,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function exportDefaulters(Request $request)
    {
        $minBalance = (float) $request->get('min_balance', 1);
        $students = Student::where('balance', '>=', $minBalance)
            ->where('status', 'active')
            ->orderByDesc('balance')
            ->get();

        $filename = 'Marell-Defaulters-' . date('Ymd-His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($students) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['ADM', 'Name', 'Class', 'Parent', 'Phone', 'Total Fee', 'Paid', 'Balance']);

            foreach ($students as $s) {
                fputcsv($out, [
                    $s->adm_no, $s->name, $s->class, $s->parent_name, $s->parent_phone,
                    number_format((float) $s->total_fee, 2, '.', ''),
                    number_format((float) $s->paid_amount, 2, '.', ''),
                    number_format((float) $s->balance, 2, '.', ''),
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function daily(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $payments = Payment::with('student')
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->get();
        $total = $payments->sum('amount');

        return view('principal.finance.daily', compact('payments', 'date', 'total'));
    }
}
