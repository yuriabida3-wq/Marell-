<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Services\MpesaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(Request $request)
    {
        $student = null;

        if ($request->filled('adm')) {
            $student = Student::where('adm_no', trim($request->adm))->first();
            if (!$student) {
                return redirect()->route('pay')->with('error', "No student found with admission number {$request->adm}.");
            }
            session(['pay_student_id' => $student->id]);
        }

        return view('public.pay', compact('student'));
    }

    public function initiate(Request $request)
    {
        $data = $request->validate([
            'adm'    => 'required|string',
            'amount' => 'required|numeric|min:1|max:500000',
            'phone'  => 'required|string',
        ]);

        $student = Student::where('adm_no', trim($data['adm']))->first();
        if (!$student) return back()->with('error', 'Student not found.');

        $phone = MpesaService::normalizePhone($data['phone']);
        if (!$phone) return back()->with('error', 'Invalid phone number. Use format 07XXXXXXXX.');

        $recent = Payment::where('student_id', $student->id)
            ->where('status', 'pending')
            ->where('amount', $data['amount'])
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();
        if ($recent) return back()->with('error', 'A payment is already in progress. Please wait for the M-Pesa prompt.');

        $payment = Payment::create([
            'student_id' => $student->id,
            'amount'     => $data['amount'],
            'method'     => 'M-Pesa',
            'status'     => 'pending',
            'term'       => 'Term 1',
            'year'       => date('Y'),
        ]);

        $mpesa = new MpesaService();
        $res   = $mpesa->stkPush($phone, (float) $data['amount'], $student->adm_no, 'Fees ' . $student->adm_no);

        if (isset($res['error'])) {
            $payment->update(['status' => 'failed', 'notes' => $res['error']]);
            return back()->with('error', 'M-Pesa error: ' . $res['error']);
        }

        if (!isset($res['CheckoutRequestID'])) {
            $payment->update(['status' => 'failed', 'notes' => 'No CheckoutRequestID']);
            return back()->with('error', 'Daraja did not return a checkout ID. Please try again.');
        }

        $payment->update(['checkout_request_id' => $res['CheckoutRequestID']]);

        return redirect()->route('pay.wait', ['payment' => $payment->id]);
    }

    public function wait(Payment $payment)
    {
        return view('public.pay-wait', compact('payment'));
    }

    public function check(Payment $payment)
    {
        return response()->json([
            'status'      => $payment->status,
            'transaction' => $payment->transaction_code,
            'receipt_no'  => $payment->receipt_no,
            'redirect'    => $payment->status === 'completed' ? route('pay.success', $payment) : null,
        ]);
    }

    public function success(Payment $payment)
    {
        if ($payment->status !== 'completed') {
            return redirect()->route('pay')->with('error', 'Payment not completed yet.');
        }
        return view('public.pay-success', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        if ($payment->status !== 'completed') {
            abort(404);
        }

        $student = $payment->student;
        $pdf = Pdf::loadView('pdf.receipt', compact('payment', 'student'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Marell-Receipt-' . $payment->receipt_no . '.pdf');
    }
}
