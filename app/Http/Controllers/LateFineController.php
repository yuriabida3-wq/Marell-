<?php

namespace App\Http\Controllers;

use App\Models\LateFine;
use App\Models\Student;
use App\Models\AuditLog;
use App\Services\LateFineService;
use Illuminate\Http\Request;

class LateFineController extends Controller
{
    public function index(Request $request)
    {
        $query = LateFine::with('student')->latest('applied_date');
        if ($request->boolean('active')) $query->where('waived', false);

        $fines = $query->paginate(30)->withQueryString();
        $totalActive = LateFine::where('waived', false)->sum('amount');
        $totalWaived = LateFine::where('waived', true)->sum('amount');
        $count = LateFine::where('waived', false)->count();

        return view('principal.fines.index', compact('fines', 'totalActive', 'totalWaived', 'count'));
    }

    public function apply()
    {
        $r = LateFineService::applyMonthlyFines();
        return back()->with('success', "Fines applied: {$r['applied']}, skipped: {$r['skipped']}.");
    }

    public function waive(Request $request, LateFine $fine)
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);
        LateFineService::waive($fine, $data['reason'], auth()->id());
        AuditLog::log('fine.waived', $fine, ['amount' => $fine->amount], [], $data['reason']);
        return back()->with('success', 'Fine waived.');
    }
}
