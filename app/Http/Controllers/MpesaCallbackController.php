<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMpesaCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    /**
     * Safaricom hits this endpoint with the STK Push result.
     * We MUST return {"ResultCode":0,"ResultDesc":"Accepted"} within 5 seconds
     * or Safaricom retries. So we dispatch the heavy work to a queue job.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('M-Pesa callback received', $payload);

        // Dispatch to queue — do NOT process here (would risk timing out)
        ProcessMpesaCallback::dispatch($payload)->onQueue('mpesa');

        // Immediate acknowledgement to Safaricom
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
