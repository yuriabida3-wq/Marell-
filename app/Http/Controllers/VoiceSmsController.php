<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\VoiceSmsService;
use Illuminate\Http\Request;

class VoiceSmsController extends Controller
{
    public function index()
    {
        $defaulters = Student::where('balance', '>', 0)->where('status', 'active')->count();
        return view('principal.voice-sms', compact('defaulters'));
    }

    public function bulkRemind(Request $request)
    {
        $data = $request->validate([
            'min_balance' => 'nullable|numeric|min:0',
            'limit'       => 'nullable|integer|min:1|max:200',
        ]);

        $minBalance = (float) ($data['min_balance'] ?? 1);
        $limit      = (int) ($data['limit'] ?? 50);

        $students = Student::where('balance', '>=', $minBalance)
            ->where('status', 'active')
            ->orderByDesc('balance')
            ->limit($limit)
            ->get();

        $svc = new VoiceSmsService();
        $sent = 0; $failed = 0;

        foreach ($students as $s) {
            if (!$s->parent_phone) continue;
            try {
                if ($svc->call($s->parent_phone, $svc->feeReminderMessage($s))) $sent++;
                else $failed++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return back()->with('success', "Voice calls queued. Sent: {$sent}, Failed: {$failed}.");
    }

    public function preview(Request $request)
    {
        $data = $request->validate(['adm' => 'required|string']);
        $student = Student::where('adm_no', trim($data['adm']))->first();
        if (!$student) return response()->json(['error' => 'Student not found'], 404);

        $svc = new VoiceSmsService();
        return response()->json([
            'student' => $student->name,
            'phone'   => $student->parent_phone,
            'message' => $svc->feeReminderMessage($student),
        ]);
    }
}
