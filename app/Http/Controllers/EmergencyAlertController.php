<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmergencyAlert;
use App\Models\EmergencyAlert;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class EmergencyAlertController extends Controller
{
    public function index()
    {
        $alerts = EmergencyAlert::with('sender')->latest()->paginate(20);

        $parentCount  = Student::whereNotNull('parent_phone')->distinct()->count('parent_phone');
        $teacherCount = User::role('teacher')->whereNotNull('phone')->count();

        return view('principal.emergency.index', compact('alerts', 'parentCount', 'teacherCount'));
    }

    public function create()
    {
        return view('principal.emergency.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:120',
            'message'  => 'required|string|max:1000',
            'severity' => 'required|in:info,warning,critical',
            'audience' => 'required|in:parents,teachers,both',
        ]);

        $data['status']  = 'draft';
        $data['sent_by'] = auth()->id();

        $alert = EmergencyAlert::create($data);

        // Fire the broadcast right away
        SendEmergencyAlert::dispatch($alert)->onQueue('default');

        return redirect()->route('principal.emergency.index')
            ->with('success', 'Emergency alert queued for delivery. Check status below.');
    }

    public function show(EmergencyAlert $emergency)
    {
        $emergency->load('sender');
        return view('principal.emergency.show', ['alert' => $emergency]);
    }

    public function destroy(EmergencyAlert $emergency)
    {
        $emergency->delete();
        return redirect()->route('principal.emergency.index')->with('success', 'Alert deleted.');
    }
}
