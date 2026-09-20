<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsCenterController extends Controller
{
    public function index()
    {
        $parentCount  = Student::whereNotNull('parent_phone')->distinct('parent_phone')->count('parent_phone');
        $teacherCount = User::role('teacher')->whereNotNull('phone')->count();
        $defaulterCount = Student::where('balance', '>', 0)->count();

        $recent = session('sms_recent', []);

        return view('principal.sms.index', compact('parentCount', 'teacherCount', 'defaulterCount', 'recent'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'audience' => 'required|in:parents,teachers,defaulters,custom',
            'message'  => 'required|string|max:480',
            'custom_phones' => 'nullable|string',
        ]);

        $phones = [];

        switch ($data['audience']) {
            case 'parents':
                $phones = Student::whereNotNull('parent_phone')->distinct()->pluck('parent_phone')->all();
                break;

            case 'teachers':
                $phones = User::role('teacher')->whereNotNull('phone')->pluck('phone')->all();
                break;

            case 'defaulters':
                $phones = Student::where('balance', '>', 0)->whereNotNull('parent_phone')->distinct()->pluck('parent_phone')->all();
                break;

            case 'custom':
                $raw = preg_replace('/\s+/', '', $data['custom_phones'] ?? '');
                $phones = array_filter(explode(',', $raw));
                break;
        }

        $phones = array_values(array_unique(array_filter($phones)));

        if (empty($phones)) {
            return back()->with('error', 'No recipients matched your selection.');
        }

        $sms = app(SmsService::class);
        $sent = 0;
        $failed = 0;

        foreach ($phones as $phone) {
            $normalized = \App\Services\MpesaService::normalizePhone($phone) ?: $phone;
            try {
                if ($sms->send($normalized, $data['message'])) $sent++;
                else $failed++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        // Store a quick summary in session for the UI
        $recent = session('sms_recent', []);
        array_unshift($recent, [
            'time'    => now()->format('d M Y H:i'),
            'audience'=> $data['audience'],
            'sent'    => $sent,
            'failed'  => $failed,
            'preview' => mb_substr($data['message'], 0, 60),
        ]);
        session(['sms_recent' => array_slice($recent, 0, 10)]);

        return redirect()->route('principal.sms.index')
            ->with('success', "SMS dispatched. Sent: {$sent}, Failed: {$failed}.");
    }
}
