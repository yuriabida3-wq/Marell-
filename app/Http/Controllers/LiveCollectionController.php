<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class LiveCollectionController extends Controller
{
    public function index()
    {
        return view('principal.live-collection');
    }

    public function feed(Request $request)
    {
        $since = (int) $request->get('since_id', 0);

        $todayTotal = Payment::where('status', 'completed')->whereDate('created_at', today())->sum('amount');
        $todayCount = Payment::where('status', 'completed')->whereDate('created_at', today())->count();

        $newPayments = Payment::with('student')
            ->where('status', 'completed')
            ->where('id', '>', $since)
            ->orderBy('id')
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'student' => $p->student->name ?? 'Unknown',
                'adm' => $p->student->adm_no ?? '',
                'class' => $p->student->class ?? '',
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'time' => $p->created_at->format('H:i'),
            ]);

        $recent = Payment::with('student')
            ->where('status', 'completed')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($p) => [
                'student' => $p->student->name ?? 'Unknown',
                'adm' => $p->student->adm_no ?? '',
                'class' => $p->student->class ?? '',
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'time' => $p->created_at->format('H:i:s'),
            ]);

        return response()->json([
            'today_total' => (float) $todayTotal,
            'today_count' => $todayCount,
            'new_payments' => $newPayments,
            'recent' => $recent,
            'latest_id' => $newPayments->max('id') ?: $since,
        ]);
    }
}
