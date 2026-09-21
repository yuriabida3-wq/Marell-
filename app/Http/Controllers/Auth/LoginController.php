<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login:' . strtolower($creds['email']) . '|' . $request->ip();

        // Lockout after 5 failed attempts in 15 min
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $secs = RateLimiter::availableIn($key);
            LoginActivity::record('locked_out', $creds['email'], null, "Locked for {$secs}s");
            return back()->withErrors(['email' => "Too many attempts. Try again in {$secs} seconds."])->withInput();
        }

        if (!Auth::attempt($creds, $request->boolean('remember'))) {
            RateLimiter::hit($key, 900); // 15 min window
            LoginActivity::record('login_failed', $creds['email'], null, 'Invalid credentials');
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        $user = Auth::user();

        if (!$user->active) {
            Auth::logout();
            LoginActivity::record('login_failed', $creds['email'], $user->id, 'Account inactive');
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        LoginActivity::record('login_success', $user->email, $user->id);

        return redirect()->intended($this->homeFor($user));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            LoginActivity::record('logout', $user->email, $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function homeFor($user): string
    {
        if ($user->hasRole('principal')) return '/principal';
        if ($user->hasRole('dos'))       return '/dos';
        if ($user->hasRole('bursar'))    return '/bursar';
        if ($user->hasRole('teacher'))   return '/teacher';
        return '/';
    }
}
