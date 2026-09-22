<?php

namespace App\Http\Controllers;

use App\Models\{FeeEscalation, FeeReminderSetting, Student};
use App\Services\FeeAutopilotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeAutopilotController extends Controller
{
    public function index(Request $request)
    {
        $stage = $request->get('stage');

        $query = FeeEscalation::with('student')->latest();
        if ($stage) $query->where('stage', $stage);

        $escalations = $query->paginate(40)->withQueryString();

        $stats = [
            'today'    => FeeEscalation::whereDate('created_at', today())->count(),
            'week'     => FeeEscalation::where('created_at', '>=', now()->subWeek())->count(),
            'month'    => FeeEscalation::where('created_at', '>=', now()->startOfMonth())->count(),
            'sent'     => FeeEscalation::where('status', 'sent')->count(),
            'failed'   => FeeEscalation::where('status', 'failed')->count(),
            'pending'  => FeeEscalation::where('status', 'pending')->count(),
        ];

        // By stage
        $byStage = FeeEscalation::select('stage', DB::raw('COUNT(*) as total'))
            ->groupBy('stage')
            ->orderBy('stage')
            ->get();

        $stages = FeeReminderSetting::orderBy('stage')->get();

        return view('principal.fee-autopilot.index', compact('escalations', 'stats', 'byStage', 'stages', 'stage'));
    }

    public function run()
    {
        $stats = FeeAutopilotService::runDaily();
        return back()->with('success', "Autopilot run: {$stats['escalations_sent']} sent, {$stats['skipped']} skipped.");
    }

    public function student(Student $student)
    {
        $escalations = FeeEscalation::where('student_id', $student->id)->orderBy('stage')->get();
        return view('principal.fee-autopilot.student', compact('student', 'escalations'));
    }

    public function triggerNext(Student $student)
    {
        $escalation = FeeAutopilotService::triggerNext($student);
        if (!$escalation) {
            return back()->with('error', 'All stages already triggered for this student.');
        }
        return back()->with('success', "Stage {$escalation->stage} triggered: {$escalation->title}");
    }

    public function settings()
    {
        $stages = FeeReminderSetting::orderBy('stage')->get();
        return view('principal.fee-autopilot.settings', compact('stages'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'template' => 'required|array',
            'active'   => 'nullable|array',
        ]);

        foreach ($data['template'] as $stageId => $template) {
            $setting = FeeReminderSetting::find($stageId);
            if ($setting) {
                $setting->update([
                    'template' => $template,
                    'active'   => isset($data['active'][$stageId]),
                ]);
            }
        }

        return back()->with('success', 'Reminder settings updated.');
    }
}
