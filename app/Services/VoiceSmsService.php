<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceSmsService
{
    /**
     * Send a voice call to a phone number. Uses Africa's Talking Voice API
     * when keys are present, otherwise logs a stub.
     */
    public function call(string $phone, string $messageSwahili, string $voice = 'woman'): bool
    {
        $username = config('services.africastalking.username');
        $apiKey   = config('services.africastalking.api_key');

        if (!$username || !$apiKey || $apiKey === 'your_api_key_here') {
            Log::info("[VOICE-STUB] call to {$phone}: {$messageSwahili}");
            return true;
        }

        try {
            $endpoint = $username === 'sandbox'
                ? 'https://voice.sandbox.africastalking.com/version1/call'
                : 'https://voice.africastalking.com/call';

            $res = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post($endpoint, [
                'username' => $username,
                'to'       => $phone,
                'from'     => config('services.africastalking.sender', 'MARELL'),
                'callerId' => '',
                'voice'    => $voice,
                'text'     => $messageSwahili,
            ]);

            if (!$res->successful()) {
                Log::warning("Voice call failed to {$phone}: " . $res->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Voice call exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build a Swahili voice message for a fee reminder.
     */
    public function feeReminderMessage(Student $student): string
    {
        $balance = number_format((float) $student->balance, 0);

        return "Habari, mzazi wa {$student->name}. "
            . "Salio la ada shuleni Marell Academy ni shilingi {$balance}. "
            . "Tafadhali lipa kabla ya tarehe kumi. "
            . "Asante sana. Kwa maelezo zaidi, piga simu namba sufuri saba saba moja mbili tatu nne tano sita saba.";
    }

    /**
     * Build a Swahili voice message for a payment receipt.
     */
    public function paymentReceivedMessage(Student $student, float $amount): string
    {
        $amt = number_format($amount, 0);

        return "Asante. Tumepokea malipo ya shilingi {$amt} kutoka kwa {$student->name}. "
            . "Salio lililobaki ni shilingi " . number_format((float) $student->balance, 0) . ". "
            . "Karibu tena.";
    }
}
