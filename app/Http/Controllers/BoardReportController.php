<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardReportController extends Controller
{
    public function index()
    {
        return view('principal.board-report');
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $from = $data['from'] . ' 00:00:00';
        $to   = $data['to'] . ' 23:59:59';

        $income   = Payment::where('payments.status', 'completed')
            ->whereBetween('payments.created_at', [$from, $to])
            ->sum('payments.amount');

        $expenses = Expense::whereBetween('expense_date', [$data['from'], $data['to']])->sum('amount');
        $net      = $income - $expenses;

        $byMethod = Payment::where('payments.status', 'completed')
            ->whereBetween('payments.created_at', [$from, $to])
            ->select('payments.method', DB::raw('SUM(payments.amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('payments.method')
            ->get();

        $byClass = Payment::where('payments.status', 'completed')
            ->whereBetween('payments.created_at', [$from, $to])
            ->join('students', 'students.id', '=', 'payments.student_id')
            ->select('students.class', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('students.class')
            ->orderBy('students.class')
            ->get();

        $expenseByCategory = Expense::whereBetween('expense_date', [$data['from'], $data['to']])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $trend = Payment::where('payments.status', 'completed')
            ->whereBetween('payments.created_at', [$from, $to])
            ->select(DB::raw('DATE(payments.created_at) as day'), DB::raw('SUM(payments.amount) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totalStudents = Student::where('status', 'active')->count();
        $defaulters    = Student::where('balance', '>', 0)->count();
        $outstanding   = Student::where('balance', '>', 0)->sum('balance');

        $pdf = Pdf::loadView('pdf.board-report', compact(
            'from', 'to', 'income', 'expenses', 'net',
            'byMethod', 'byClass', 'expenseByCategory', 'trend',
            'totalStudents', 'defaulters', 'outstanding'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('Board-Report-' . date('Ymd', strtotime($data['from'])) . '-to-' . date('Ymd', strtotime($data['to'])) . '.pdf');
    }
}
