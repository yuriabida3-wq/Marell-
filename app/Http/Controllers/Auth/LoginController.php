<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (!Auth::attempt($creds, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        if (!Auth::user()->active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor(Auth::user()));
    }

    public function logout(Request $request)
    {
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
