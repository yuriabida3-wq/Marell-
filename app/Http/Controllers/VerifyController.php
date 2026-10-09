<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function receipt(Request $request, string $receiptNo)
    {
        $payment = Payment::with('student')->where('receipt_no', $receiptNo)->first();

        // Full details only when the URL carries a valid signature.
        // Public visitors see a verdict, amount and date — no identity,
        // no transaction code, no staff name.
        $full = $payment !== null && $request->hasValidSignature();

        return view('public.verify-receipt', [
            'payment'   => $payment,
            'receiptNo' => $receiptNo,
            'full'      => $full,
        ]);
    }
}