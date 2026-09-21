<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public const TIMEOUT_MINUTES = 30;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $last = $request->session()->get('last_activity_at');
            $now  = time();

            if ($last && ($now - $last) > (self::TIMEOUT_MINUTES * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/login')->withErrors(['email' => 'Session expired after ' . self::TIMEOUT_MINUTES . ' minutes of inactivity.']);
            }

            $request->session()->put('last_activity_at', $now);
        }

        return $next($request);
    }
}
