<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrincipalController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        $todayCollection = Payment::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $yesterdayCollection = Payment::where('status', 'completed')
            ->whereDate('created_at', now()->subDay()->toDateString())
            ->sum('amount');

        $defaulters     = Student::where('balance', '>', 0)->count();
        $activeStudents = Student::where('status', 'active')->count();
        $teachers       = User::role('teacher')->count();

        // Fees Trend — last 7 days
        $trend = Payment::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('D d M');
            $series[] = (float) ($trend[$d]->total ?? 0);
        }

        $recentPayments = Payment::with('student')->latest()->take(10)->get();

        $classDist = Student::select('class', DB::raw('COUNT(*) as total'))
            ->groupBy('class')
            ->orderBy('class')
            ->get();

        return view('principal.dashboard', compact(
            'todayCollection', 'yesterdayCollection',
            'defaulters', 'activeStudents', 'teachers',
            'labels', 'series', 'recentPayments', 'classDist'
        ));
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) return response()->json(['results' => []]);

        $students = Student::where('name', 'like', "%{$q}%")
            ->orWhere('adm_no', 'like', "%{$q}%")
            ->orWhere('parent_phone', 'like', "%{$q}%")
            ->orWhere('parent_name', 'like', "%{$q}%")
            ->take(8)
            ->get();

        $payments = Payment::with('student')
            ->where('transaction_code', 'like', "%{$q}%")
            ->orWhere('receipt_no', 'like', "%{$q}%")
            ->take(5)
            ->get();

        $results = [];

        foreach ($students as $s) {
            $results[] = [
                'title'    => "🎓 {$s->name}",
                'subtitle' => "{$s->adm_no} · {$s->class} · Balance: KES " . number_format($s->balance, 0),
                'url'      => "/principal/students/{$s->id}",
            ];
        }

        foreach ($payments as $p) {
            $results[] = [
                'title'    => "💰 " . ($p->student->name ?? 'Unknown') . " — KES " . number_format($p->amount, 0),
                'subtitle' => "Receipt: {$p->receipt_no} · Txn: {$p->transaction_code} · {$p->status}",
                'url'      => "/principal/finance?q={$p->receipt_no}",
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Native CSV export — no Maatwebsite dependency, works on PHP 8.5.
     */
    public function exportStudents(Request $request)
    {
        $class    = $request->get('class');
        $filename = 'Marell-Students-' . ($class ? str_replace(' ', '-', $class) : 'All') . '-' . date('Ymd') . '.csv';

        $query = Student::query();
        if ($class) $query->where('class', $class);
        $students = $query->orderBy('class')->orderBy('name')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($students) {
            $out = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'ADM No', 'Name', 'Class', 'Stream', 'Parent', 'Phone',
                'Total Fee', 'Paid', 'Balance', 'Status',
            ]);

            foreach ($students as $s) {
                fputcsv($out, [
                    $s->adm_no,
                    $s->name,
                    $s->class,
                    $s->stream,
                    $s->parent_name,
                    $s->parent_phone,
                    number_format((float) $s->total_fee, 2, '.', ''),
                    number_format((float) $s->paid_amount, 2, '.', ''),
                    number_format((float) $s->balance, 2, '.', ''),
                    $s->status,
                ]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
