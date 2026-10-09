<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuardOrStaff
{
    /**
     * Allow the request if the caller is:
     *   - a logged-in staff user (Auth), OR
     *   - a guard with an active guard session (session('guard_id')).
     *
     * Deny everyone else. Parents are explicitly NOT allowed through
     * this middleware, per CP-01 decision.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isStaff = auth()->check();
        $isGuard = (bool) session('guard_id');

        if ($isStaff || $isGuard) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Not authorized'], 401);
        }

        return redirect()->route('login');
    }
}
