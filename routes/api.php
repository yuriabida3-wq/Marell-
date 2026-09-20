<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaCallbackController;

/*
 * M-Pesa callback — NO auth, NO CSRF.
 * Safaricom posts the STK result here after the customer enters their PIN.
 */
Route::post('/mpesa/callback', [MpesaCallbackController::class, 'handle']);

// Optional: sandbox test endpoint
Route::get('/mpesa/ping', fn () => response()->json(['ok' => true, 'ts' => now()->toIso8601String()]));
