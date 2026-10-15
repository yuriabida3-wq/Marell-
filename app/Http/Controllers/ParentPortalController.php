<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Services\MpesaService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

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

        // Per-phone send limit, applied BEFORE any registration check so the
        // response shape is uniform. 3 requests per 5 minutes.
        $sendKey = 'parent-otp-send:' . $phone;
        if (RateLimiter::tooManyAttempts($sendKey, 3)) {
            $secs = RateLimiter::availableIn($sendKey);
            return back()->with('error', "Too many requests. Please try again in {$secs} seconds.");
        }
        RateLimiter::hit($sendKey, 300);

        $studentExists = Student::where('parent_phone', $phone)->exists();

        if ($studentExists) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put("parent_otp:{$phone}", Hash::make($otp), now()->addMinutes(5));

            try {
                app(SmsService::class)->send(
                    $phone,
                    "Your Marell Parent Portal code is: {$otp}\nValid for 5 minutes. Do not share."
                );
            } catch (\Throwable $e) {
                \Log::warning('OTP SMS failed: ' . \App\Support\LogRedactor::maskPhonesInString($e->getMessage()));
            }
        }

        // Set the pending session for BOTH branches so the verify page renders
        // identically whether or not the number is registered.
        session(['parent_pending_phone' => $phone]);

        return redirect()->route('parent.verify')->with('success', 'If that number is registered, an OTP has been sent.');
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

        // Per-phone verify limit: 5 failed attempts per 15 minutes.
        $verifyKey = 'parent-otp-verify:' . $phone;
        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            Cache::forget("parent_otp:{$phone}");
            return back()->with('error', 'Too many attempts. Please request a new code.');
        }

        $cached = Cache::get("parent_otp:{$phone}");
        if (!$cached || !Hash::check((string) $data['otp'], (string) $cached)) {
            RateLimiter::hit($verifyKey, 900);
            return back()->with('error', 'Invalid or expired code. Please try again.');
        }

        RateLimiter::clear($verifyKey);
        Cache::forget("parent_otp:{$phone}");
        session()->forget('parent_pending_phone');
        $request->session()->regenerate();
        session(['parent_phone' => $phone]);

        return redirect()->route('parent.dashboard');
    }

    public function logout(Request $request)
    {
        session()->forget('parent_phone');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
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
