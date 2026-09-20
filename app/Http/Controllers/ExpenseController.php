<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $expenses = Expense::with('recorder')->whereBetween('expense_date', [$from, $to])->orderByDesc('expense_date')->paginate(30)->withQueryString();

        $totalExpenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $totalIncome   = Payment::where('status','completed')->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->sum('amount');
        $net           = $totalIncome - $totalExpenses;

        return view('principal.expenses.index', compact('expenses','from','to','totalExpenses','totalIncome','net'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'     => 'required|string|max:60',
            'description'  => 'required|string|max:180',
            'amount'       => 'required|numeric|min:1',
            'method'       => 'required|in:Cash,Bank,M-Pesa',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $data['recorded_by'] = auth()->id();
        Expense::create($data);

        return back()->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $expenses = Expense::whereBetween('expense_date', [$from, $to])->orderBy('expense_date')->get();

        $filename = "Marell-Expenses-{$from}-to-{$to}.csv";
        return response()->streamDownload(function () use ($expenses) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Date','Category','Description','Method','Amount','Notes']);
            foreach ($expenses as $e) {
                fputcsv($out, [$e->expense_date->format('Y-m-d'), $e->category, $e->description, $e->method, number_format((float)$e->amount, 2, '.', ''), $e->notes]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
