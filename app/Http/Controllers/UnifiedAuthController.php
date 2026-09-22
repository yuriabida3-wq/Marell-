<?php

namespace App\Http\Controllers;

use App\Models\{LoginActivity, Student, User};
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Cache, Hash, RateLimiter};

class UnifiedAuthController extends Controller
{
    /**
     * Show the unified login page.
     */
    public function show()
    {
        // Already logged in? Redirect to their home
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }
        // Guard already logged in via session?
        if (session('guard_id')) {
            return redirect()->route('gate.console.index');
        }
        // Parent already logged in?
        if (session('parent_phone')) {
            return redirect()->route('parent.dashboard');
        }

        return view('auth.unified-login');
    }

    /**
     * Staff login (email + password).
     */
    public function staffLogin(Request $request)
    {
        $creds = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login:' . strtolower($creds['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $secs = RateLimiter::availableIn($key);
            LoginActivity::record('locked_out', $creds['email'], null, "Locked for {$secs}s");
            return back()->withErrors(['email' => "Too many attempts. Try again in {$secs} seconds."])->withInput();
        }

        if (!Auth::attempt($creds, $request->boolean('remember'))) {
            RateLimiter::hit($key, 900);
            LoginActivity::record('login_failed', $creds['email'], null, 'Invalid credentials');
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $user = Auth::user();
        if (!$user->active) {
            Auth::logout();
            LoginActivity::record('login_failed', $creds['email'], $user->id, 'Account inactive');
            return back()->withErrors(['email' => 'Your account has been deactivated. Contact the Director.'])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        LoginActivity::record('login_success', $user->email, $user->id);

        return redirect($this->homeFor($user));
    }

    /**
     * Guard login (phone + 4-digit PIN).
     */
    public function guardLogin(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'pin'   => 'required|string|min:4|max:8',
        ]);

        $phone = $this->normalize($data['phone']);

        $user = User::where('phone', $phone)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['security', 'principal', 'dos', 'bursar']))
            ->where('active', true)
            ->first();

        if (!$user) {
            return back()->withErrors(['guard_phone' => 'No account found with that phone number.'])->withInput();
        }

        if (!$user->pin || !Hash::check($data['pin'], $user->pin)) {
            return back()->withErrors(['guard_pin' => 'Invalid PIN.'])->withInput();
        }

        session([
            'guard_id'   => $user->id,
            'guard_name' => $user->name,
        ]);

        return redirect()->route('gate.console.index');
    }

    /**
     * Parent OTP — request code.
     */
    public function parentSendOtp(Request $request)
    {
        $data = $request->validate(['phone' => 'required|string']);

        $phone = $this->normalize($data['phone']);
        if (!$phone) {
            return back()->withErrors(['parent_phone' => 'Invalid phone number format.'])->withInput();
        }

        // Check if phone belongs to any student
        $studentExists = Student::where('parent_phone', $phone)->exists();
        if (!$studentExists) {
            return back()->withErrors(['parent_phone' => 'No student is registered with that phone number.'])->withInput();
        }

        // Rate limit
        $key = 'parent-otp:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $secs = RateLimiter::availableIn($key);
            return back()->withErrors(['parent_phone' => "Too many requests. Try again in {$secs} seconds."])->withInput();
        }
        RateLimiter::hit($key, 300);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("parent_otp:{$phone}", $otp, now()->addMinutes(5));

        try {
            app(SmsService::class)->send(
                $phone,
                "Your Marell Parent Portal code is: {$otp}\nValid for 5 minutes. Do not share."
            );
        } catch (\Throwable $e) {
            \Log::warning('OTP SMS failed: ' . $e->getMessage());
        }

        session(['parent_pending_phone' => $phone]);

        return redirect()->route('login.hub')->with('otp_sent', true);
    }

    /**
     * Parent OTP — verify code.
     */
    public function parentVerifyOtp(Request $request)
    {
        $data = $request->validate(['otp' => 'required|string|size:6']);

        $phone = session('parent_pending_phone');
        if (!$phone) {
            return back()->withErrors(['otp' => 'Session expired. Try again.'])->withInput();
        }

        $cached = Cache::get("parent_otp:{$phone}");
        if (!$cached || $cached !== $data['otp']) {
            return back()->withErrors(['otp' => 'Invalid or expired code.'])->withInput();
        }

        Cache::forget("parent_otp:{$phone}");
        session()->forget('parent_pending_phone');
        session(['parent_phone' => $phone]);

        return redirect()->route('parent.dashboard');
    }

    /**
     * Unified logout — clears all session types.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            LoginActivity::record('logout', $user->email, $user->id);
        }

        Auth::logout();
        $request->session()->forget(['guard_id', 'guard_name', 'parent_phone', 'parent_pending_phone']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Which dashboard for the role.
     */
    protected function homeFor(User $user): string
    {
        if ($user->hasRole('principal')) return '/principal';
        if ($user->hasRole('dos'))       return '/dos';
        if ($user->hasRole('bursar'))    return '/bursar';
        if ($user->hasRole('teacher'))   return '/teacher';
        if ($user->hasRole('security'))  return '/gate/console';
        return '/';
    }

    protected function normalize(string $phone): ?string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) $p = '254' . substr($p, 1);
        if (strlen($p) === 9)         $p = '254' . $p;
        return strlen($p) >= 12 ? $p : null;
    }
}
