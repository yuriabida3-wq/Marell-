<?php

namespace App\Http\Controllers;

use App\Jobs\DemoAutoCompletePayment;
use App\Models\Payment;
use App\Models\Student;
use App\Services\MpesaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\RateLimiter;

class PaymentController extends Controller
{
    public function show(Request $request)
    {
        $student = null;

        if ($request->filled('adm')) {
            $found = Student::where('adm_no', trim($request->adm))->first();
            if (!$found) {
                return redirect()->route('pay')->with('error', 'No student found with that admission number.');
            }
            session(['pay_student_id' => $found->id]);

            // Mask everything except what a parent needs to confirm the child.
            // Full details appear only on the success page after payment.
            $student = [
                'adm_no'   => $found->adm_no,
                'initials' => $this->initials($found->name),
                'class'    => trim($found->class . ' ' . $found->stream),
            ];
        }

        return view('public.pay', compact('student'));
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $out = [];
        foreach ($parts as $p) {
            if ($p !== '') {
                $out[] = strtoupper(substr($p, 0, 1)) . '.';
            }
        }
        return implode(' ', $out);
    }

    public function initiate(Request $request)
    {
        if (!\App\Support\PaymentSafety::paymentsAllowed()) {
            return back()->with('error', \App\Support\PaymentSafety::blockReason());
        }

        $data = $request->validate([
            'adm'    => 'required|string',
            'amount' => 'required|numeric|min:1|max:500000',
            'phone'  => 'required|string',
        ]);

        $student = Student::where('adm_no', trim($data['adm']))->first();
        if (!$student) return back()->with('error', 'Student not found.');

        $phone = MpesaService::normalizePhone($data['phone']);
        if (!$phone) return back()->with('error', 'Invalid phone number. Use format 07XXXXXXXX.');

        // CP-27: three rate limits on an unauthenticated payment endpoint.
        // - per phone: blocks a single phone being spammed with STK prompts
        // - per admission: blocks one student's account being spammed
        // - global: bounds total requests so Africa's Talking / Safaricom
        //   cost cannot be driven up by a distributed flood.
        $phoneKey  = 'pay-init:phone:' . $phone;
        $admKey    = 'pay-init:adm:' . strtoupper(trim($data['adm']));
        $globalKey = 'pay-init:global';

        if (RateLimiter::tooManyAttempts($globalKey, 200)) {
            return back()->with('error', 'Payment service is busy. Please try again shortly.');
        }
        if (RateLimiter::tooManyAttempts($phoneKey, 5)) {
            $secs = RateLimiter::availableIn($phoneKey);
            return back()->with('error', "Too many attempts for this phone. Try again in {$secs} seconds.");
        }
        if (RateLimiter::tooManyAttempts($admKey, 5)) {
            $secs = RateLimiter::availableIn($admKey);
            return back()->with('error', "Too many attempts for this admission number. Try again in {$secs} seconds.");
        }

        RateLimiter::hit($globalKey, 3600);
        RateLimiter::hit($phoneKey, 900);
        RateLimiter::hit($admKey, 900);

        // Prevent double submit
        $recent = Payment::where('student_id', $student->id)
            ->where('status', 'pending')
            ->where('amount', $data['amount'])
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();
        if ($recent) return back()->with('error', 'A payment is already in progress. Please wait.');

        // Create pending payment
        $payment = Payment::create([
            'student_id'       => $student->id,
            'amount'           => $data['amount'],
            'initiated_amount' => $data['amount'],
            'method'     => 'M-Pesa',
            'status'     => 'pending',
            'term'       => 'Term 1',
            'year'       => date('Y'),
        ]);

        $skipStk = config('services.mpesa_skip_stk', false);

        if ($skipStk) {
            // === DEMO MODE — skip Safaricom, fake the CheckoutRequestID ===
            $fakeCheckout = 'DEMO_' . strtoupper(bin2hex(random_bytes(8)));

            $payment->update(['checkout_request_id' => $fakeCheckout]);

            // Auto-complete after 3 seconds
            DemoAutoCompletePayment::dispatch($payment)
                ->delay(now()->addSeconds(3))
                ->onQueue('default');

            return redirect()->to(URL::signedRoute('pay.wait', ['payment' => $payment->id]));
        }

        // === PRODUCTION MODE — real STK Push ===
        $mpesa = new MpesaService();
        $res   = $mpesa->stkPush($phone, (float) $data['amount'], $student->adm_no, 'Fees ' . $student->adm_no);

        if (isset($res['error'])) {
            $payment->update(['status' => 'under_reconciliation', 'notes' => 'STK error: ' . $res['error'] . ' — held for review']);
            return back()->with('error', 'M-Pesa error: ' . $res['error']);
        }

        if (!isset($res['CheckoutRequestID'])) {
            $payment->update(['status' => 'under_reconciliation', 'notes' => 'No CheckoutRequestID — held for review']);
            return back()->with('error', 'Daraja did not return a checkout ID. Please try again.');
        }

        $payment->update(['checkout_request_id' => $res['CheckoutRequestID']]);

        return redirect()->to(URL::signedRoute('pay.wait', ['payment' => $payment->id]));
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
            'redirect'    => $payment->status === 'completed' ? URL::signedRoute('pay.success', $payment) : null,
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
