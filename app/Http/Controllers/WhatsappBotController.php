<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappBotController extends Controller
{
    /**
     * Webhook receiver. Handles:
     *   - Verification handshake (GET with hub.challenge)
     *   - Incoming messages (POST)
     *
     * Supports Meta Cloud API + Twilio WhatsApp payloads.
     */
    public function verify(Request $request)
    {
        $mode      = $request->get('hub_mode');
        $token     = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function incoming(Request $request)
    {
        Log::info('WhatsApp incoming', $request->all());

        // Try Meta Cloud API payload
        $message = $request->input('entry.0.changes.0.value.messages.0.text.body')
            ?? $request->input('Body')
            ?? $request->input('message', '');

        $from = $request->input('entry.0.changes.0.value.messages.0.from')
            ?? $request->input('From')
            ?? $request->input('from', '');

        if (!$message || !$from) {
            return response()->json(['ok' => true]);
        }

        $reply = $this->handleMessage($from, trim($message));

        return response()->json([
            'ok' => true,
            'reply' => $reply,
        ]);
    }

    /**
     * Parse commands like:
     *   "balance MRL001"  → show balance
     *   "receipt 12345"   → show last receipt
     *   "help"            → help menu
     *   "results MRL001"  → latest exam mean
     */
    public function handleMessage(string $fromPhone, string $text): string
    {
        $normalized = strtolower(preg_replace('/\s+/', ' ', $text));
        $parts = explode(' ', $normalized);
        $cmd   = $parts[0] ?? '';
        $arg   = strtoupper($parts[1] ?? '');

        // Find parent by phone
        $cleanPhone = preg_replace('/\D/', '', $fromPhone);
        if (str_starts_with($cleanPhone, '0')) $cleanPhone = '254' . substr($cleanPhone, 1);

        $students = Student::where('parent_phone', $cleanPhone)->get();

        if ($students->isEmpty() && $cmd !== 'help' && $cmd !== 'hi' && $cmd !== 'hello') {
            return "Karibu Marell Academy!\nYour number is not registered. Call +254 700 000 000 for assistance.";
        }

        switch ($cmd) {
            case 'help':
            case 'hi':
            case 'hello':
                return "MARELL BOT\n"
                    . "Commands:\n"
                    . "balance [ADM]  → show fee balance\n"
                    . "receipt [ADM]  → latest receipt number\n"
                    . "results [ADM]  → exam performance\n"
                    . "pay [ADM]      → payment link\n"
                    . "help           → this menu";

            case 'balance':
            case 'salio':
                return $this->balanceReply($students, $arg);

            case 'receipt':
            case 'risiti':
                return $this->receiptReply($students, $arg);

            case 'results':
            case 'matokeo':
                return $this->resultsReply($students, $arg);

            case 'pay':
            case 'lipa':
                $s = $students->firstWhere('adm_no', $arg) ?? $students->first();
                return "Pay fees for {$s->name}:\n" . url('/pay?adm=' . $s->adm_no);

            default:
                return "Sijui amri hiyo. Andika 'help' kuona menu.";
        }
    }

    protected function balanceReply($students, string $arg): string
    {
        $student = $arg ? $students->firstWhere('adm_no', $arg) : $students->first();
        if (!$student) return "Samahani, sijaipata ADM hiyo.";

        return "MARELL ACADEMY\n"
            . "Student: {$student->name}\n"
            . "ADM: {$student->adm_no}\n"
            . "Class: {$student->class} {$student->stream}\n"
            . "----------------\n"
            . "Total Fee: KES " . number_format($student->total_fee, 2) . "\n"
            . "Paid:      KES " . number_format($student->paid_amount, 2) . "\n"
            . "Balance:   KES " . number_format($student->balance, 2) . "\n"
            . "----------------\n"
            . "Pay: " . url('/pay?adm=' . $student->adm_no);
    }

    protected function receiptReply($students, string $arg): string
    {
        $student = $arg ? $students->firstWhere('adm_no', $arg) : $students->first();
        if (!$student) return "Samahani, sijaipata ADM hiyo.";

        $last = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$last) return "Hakuna malipo yaliyorekodiwa kwa {$student->name}.";

        return "MARELL RECEIPT\n"
            . "Student: {$student->name}\n"
            . "Receipt: {$last->receipt_no}\n"
            . "Amount:  KES " . number_format($last->amount, 2) . "\n"
            . "Method:  {$last->method}\n"
            . "Date:    {$last->created_at->format('d M Y H:i')}\n"
            . "Verify:  " . url('/verify-receipt/' . $last->receipt_no);
    }

    protected function resultsReply($students, string $arg): string
    {
        $student = $arg ? $students->firstWhere('adm_no', $arg) : $students->first();
        if (!$student) return "Samahani, sijalipata ADM hiyo.";

        $results = \App\Models\Result::where('student_id', $student->id)->orderBy('subject')->get();
        if ($results->isEmpty()) return "Hakuna matokeo yaliyotolewa bado kwa {$student->name}.";

        $avg = round($results->avg('marks'), 1);

        return "MARELL RESULTS\n"
            . "Student: {$student->name}\n"
            . "Subjects: {$results->count()}\n"
            . "Average: {$avg}%\n"
            . "Full: " . url('/results?adm=' . $student->adm_no);
    }
}
