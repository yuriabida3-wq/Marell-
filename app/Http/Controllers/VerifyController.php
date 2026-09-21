<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class VerifyController extends Controller
{
    public function receipt($receiptNo)
    {
        $payment = Payment::with('student')->where('receipt_no', $receiptNo)->first();
        return view('public.verify-receipt', compact('payment', 'receiptNo'));
    }
}
