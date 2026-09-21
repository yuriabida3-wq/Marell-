<?php

namespace App\Http\Controllers;

use App\Services\TimetableGeneratorService;
use Illuminate\Http\Request;

class TimetableGeneratorController extends Controller
{
    public function index()
    {
        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $years = [date('Y'), date('Y') + 1];
        return view('dos.timetable-generator.index', compact('terms', 'years'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'term' => 'required|string|in:Term 1,Term 2,Term 3',
            'year' => 'required|string|max:10',
        ]);

        $svc = new TimetableGeneratorService();
        $result = $svc->generateForSchool($data['term'], $data['year']);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success',
            "Generated {$result['slots']} slots across {$result['classes']} classes for {$data['term']} {$data['year']}. " .
            ($result['unassigned'] > 0 ? "⚠️ {$result['unassigned']} slots without teacher." : "✅ All slots have teachers.")
        );
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'term' => 'required|string',
            'year' => 'required|string',
        ]);

        $svc = new TimetableGeneratorService();
        $clashes = $svc->verify($data['term'], $data['year']);
        $load = $svc->teacherLoad($data['term'], $data['year']);

        return view('dos.timetable-generator.verify', [
            'clashes' => $clashes,
            'load'    => $load,
            'term'    => $data['term'],
            'year'    => $data['year'],
        ]);
    }
}
