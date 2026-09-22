<?php

namespace App\Http\Controllers;

use App\Models\{Student, Payment, ApprovedPickup};
use Illuminate\Http\Request;

class QrDemoController extends Controller
{
    /**
     * QR Hub — for demo. Shows all QR types with live scanner link.
     */
    public function index()
    {
        // Get one sample of each
        $student = Student::whereNotNull('qr_token')->first();
        $payment = Payment::where('status', 'completed')->whereNotNull('receipt_no')->first();
        $pickup  = ApprovedPickup::where('active', true)->first();

        return view('qr.hub', compact('student', 'payment', 'pickup'));
    }

    /**
     * Generate QR image data URL using quickchart.io (no auth, works everywhere).
     */
    public static function qrImageUrl(string $data, int $size = 250): string
    {
        return 'https://quickchart.io/qr?text=' . urlencode($data) . '&size=' . $size . '&margin=1&ecLevel=M';
    }
}
