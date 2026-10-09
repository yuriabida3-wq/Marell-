<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaCallbackController;

/*
 * M-Pesa callbacks — no CSRF, no session auth (Safaricom posts server-to-server).
 *
 * CP-02 gates:
 *   - secure path with shared secret  -> POST /mpesa/cb/{secret}
 *   - legacy path (cutover window)    -> POST /mpesa/callback (flag-gated)
 *   - throttle 120/min per IP
 *
 * The controller dispatches to the queue; the queue worker does the money work.
 */

// Secure path — preferred. Fails closed if MPESA_CALLBACK_SECRET is unset.
Route::post('/mpesa/cb/{secret}', [MpesaCallbackController::class, 'handleSecure'])
    ->middleware('throttle:120,1');

// Legacy path — kept during Daraja cutover, gated by MPESA_CALLBACK_LEGACY_ENABLED.
Route::post('/mpesa/callback', [MpesaCallbackController::class, 'handle'])
    ->middleware('throttle:120,1');

// Sandbox ping — untouched by CP-02.
Route::get('/mpesa/ping', fn () => response()->json(['ok' => true, 'ts' => now()->toIso8601String()]));
