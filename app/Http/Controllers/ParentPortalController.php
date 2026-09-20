<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Services\MpesaService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ParentPortalController extends Controller
{
    // ---------- LOGIN ----------
    public function loginForm()
    {
        return view('public.parent-login');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = MpesaService::normalizePhone($data['phone']);
        if (!$phone) {
            return back()->with('error', 'Invalid phone number format. Use 07XXXXXXXX.');
        }

        // Check if phone belongs to any student's parent
        $studentExists = Student::where('parent_phone', $phone)->exists();
        if (!$studentExists) {
            return back()->with('error', 'No student is registered with that phone number. Please contact the school office.');
        }

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Cache for 5 minutes
        Cache::put("parent_otp:{$phone}", $otp, now()->addMinutes(5));

        // Send via SMS
        try {
            app(SmsService::class)->send(
                $phone,
                "Your Marell Parent Portal code is: {$otp}\nValid for 5 minutes. Do not share."
            );
        } catch (\Throwable $e) {
            \Log::warning('OTP SMS failed: ' . $e->getMessage());
        }

        session(['parent_pending_phone' => $phone]);

        return redirect()->route('parent.verify')->with('success', 'We sent a 6-digit code to ' . $phone);
    }

    // ---------- VERIFY OTP ----------
    public function verifyForm()
    {
        if (!session('parent_pending_phone')) {
            return redirect()->route('parent.login');
        }
        return view('public.parent-verify');
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $phone = session('parent_pending_phone');
        if (!$phone) {
            return redirect()->route('parent.login')->with('error', 'Session expired. Please try again.');
        }

        $cached = Cache::get("parent_otp:{$phone}");
        if (!$cached || $cached !== $data['otp']) {
            return back()->with('error', 'Invalid or expired code. Please try again.');
        }

        // Success: log in
        Cache::forget("parent_otp:{$phone}");
        session()->forget('parent_pending_phone');
        session(['parent_phone' => $phone]);

        return redirect()->route('parent.dashboard');
    }

    public function logout(Request $request)
    {
        session()->forget('parent_phone');
        return redirect()->route('parent.login')->with('success', 'You have been logged out.');
    }

    // ---------- DASHBOARD ----------
    public function dashboard(Request $request)
    {
        $phone = session('parent_phone');

        $children = Student::where('parent_phone', $phone)->get();

        if ($children->isEmpty()) {
            session()->forget('parent_phone');
            return redirect()->route('parent.login')->with('error', 'No children linked to this phone number.');
        }

        $selected = null;
        $results  = collect();
        $payments = collect();

        $childId = $request->get('child', $children->first()->id);
        $selected = $children->firstWhere('id', (int) $childId) ?? $children->first();

        if ($selected) {
            $results  = Result::where('student_id', $selected->id)->orderBy('subject')->get();
            $payments = Payment::where('student_id', $selected->id)
                ->where('status', 'completed')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('public.parent-dashboard', compact('children', 'selected', 'results', 'payments', 'phone'));
    }
}
