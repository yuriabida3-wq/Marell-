<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ParentAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('parent_phone')) {
            return redirect()->route('parent.login')->with('error', 'Please log in to access the Parent Portal.');
        }
        return $next($request);
    }
}
