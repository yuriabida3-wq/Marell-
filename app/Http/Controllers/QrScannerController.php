<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class QrScannerController extends Controller
{
    public function index()
    {
        return view('qr.scanner');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'receipt_no' => 'required|string|max:100',
        ]);

        // Accept either plain receipt number or a full URL (from QR)
        $input = trim($data['receipt_no']);

        // If it's a URL, extract the last segment
        if (str_contains($input, '/')) {
            $parts = explode('/', rtrim($input, '/'));
            $input = end($parts);
        }

        $payment = Payment::with('student')->where('receipt_no', $input)->first();

        if (!$payment) {
            return response()->json([
                'status'  => 'invalid',
                'message' => "No receipt with number {$input} exists in our records. This may be a fake receipt.",
                'receipt' => $input,
            ]);
        }

        if ($payment->status !== 'completed') {
            return response()->json([
                'status'  => 'pending',
                'message' => "Receipt {$input} exists but payment is {$payment->status}.",
                'receipt' => $input,
            ]);
        }

        return response()->json([
            'status'  => 'verified',
            'message' => 'Receipt verified successfully.',
            'receipt' => $input,
            'data'    => [
                'student_name'     => $payment->student->name ?? '—',
                'adm_no'           => $payment->student->adm_no ?? '—',
                'class'            => ($payment->student->class ?? '') . ' ' . ($payment->student->stream ?? ''),
                'amount'           => number_format((float) $payment->amount, 2),
                'method'           => $payment->method,
                'transaction_code' => $payment->transaction_code ?: '—',
                'date_issued'      => $payment->created_at->format('d M Y H:i'),
                'recorded_by'      => $payment->recorded_by ? 'Staff #' . $payment->recorded_by : 'System (M-Pesa)',
            ],
        ]);
    }
}
