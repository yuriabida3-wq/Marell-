<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        // Session-scoped key so repeat users get a stable log
        $sessionKey = $request->session()->get('assistant_key');
        if (!$sessionKey) {
            $sessionKey = bin2hex(random_bytes(16));
            $request->session()->put('assistant_key', $sessionKey);
        }

        $result = AssistantService::reply($data['message'], $sessionKey, $request->ip());

        return response()->json($result);
    }
}
