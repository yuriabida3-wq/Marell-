@extends('layouts.admin')
@section('title', 'Expenses')
@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div><h1 class="text-2xl md:text-3xl font-extrabold text-navy">Expenses</h1><p class="text-sm text-gray-500 mt-1">Track school expenditures</p></div>
  <a href="{{ route('principal.expenses.export', ['from'=>$from,'to'=>$to]) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold">📊 Export CSV</a>
</div>
@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>@endif

<div class="grid grid-cols-3 gap-3 mb-4">
  <div class="bg-green-50 rounded-2xl p-4 shadow"><div class="text-[10px] text-gray-600 tracking-widest font-bold">INCOME</div><div class="text-xl md:text-2xl font-extrabold text-green-700 mt-1">KES {{ number_format($totalIncome, 0) }}</div></div>
  <div class="bg-red-50 rounded-2xl p-4 shadow"><div class="text-[10px] text-gray-600 tracking-widest font-bold">EXPENSES</div><div class="text-xl md:text-2xl font-extrabold text-red-600 mt-1">KES {{ number_format($totalExpenses, 0) }}</div></div>
  <div class="{{ $net >= 0 ? 'bg-navy text-white' : 'bg-red-600 text-white' }} rounded-2xl p-4 shadow"><div class="text-[10px] tracking-widest font-bold {{ $net >= 0 ? 'text-gold' : '' }}">NET</div><div class="text-xl md:text-2xl font-extrabold mt-1">KES {{ number_format($net, 0) }}</div></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="md:col-span-2">
    <div class="bg-white rounded-2xl p-4 shadow mb-4">
      <form method="GET" class="flex gap-2 flex-wrap">
        <input type="date" name="from" value="{{ $from }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        <input type="date" name="to" value="{{ $to }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        <button class="px-4 rounded-xl bg-navy text-white text-sm font-semibold">Filter</button>
      </form>
    </div>
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500"><tr class="text-left"><th class="p-3">DATE</th><th class="p-3">CATEGORY</th><th class="p-3">DESCRIPTION</th><th class="p-3 text-right">AMOUNT</th><th class="p-3 text-right"></th></tr></thead>
        <tbody class="divide-y">
          @forelse ($expenses as $e)
            <tr class="hover:bg-gray-50">
              <td class="p-3 text-xs text-gray-500">{{ $e->expense_date->format('d M Y') }}</td>
              <td class="p-3"><span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-navy">{{ $e->category }}</span></td>
              <td class="p-3 text-navy">{{ $e->description }}<div class="text-xs text-gray-500">{{ $e->method }} · {{ $e->recorder->name ?? '—' }}</div></td>
              <td class="p-3 text-right font-bold text-red-600">KES {{ number_format($e->amount, 0) }}</td>
              <td class="p-3 text-right"><form method="POST" action="{{ route('principal.expenses.destroy', $e) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-xs text-red-600 hover:underline">✕</button></form></td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-12 text-center text-gray-400">No expenses in this period</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $expenses->links() }}</div>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow h-fit">
    <h2 class="font-extrabold text-navy mb-4">➕ Record Expense</h2>
    <form method="POST" action="{{ route('principal.expenses.store') }}" class="space-y-3">
      @csrf
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CATEGORY *</label>
        <select name="category" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          <option>Salaries</option><option>Utilities</option><option>Food &amp; Catering</option><option>Maintenance</option><option>Transport</option><option>Stationery</option><option>Marketing</option><option>Bank Charges</option><option>Other</option>
        </select>
      </div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">DESCRIPTION *</label><input name="description" required maxlength="180" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4"></div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT *</label><input name="amount" type="number" min="1" step="1" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4"></div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">METHOD</label><select name="method" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4"><option>Cash</option><option>Bank</option><option>M-Pesa</option></select></div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">DATE *</label><input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4"></div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NOTES</label><textarea name="notes" rows="2" class="w-full rounded-xl border-2 border-gray-200 px-4 py-2"></textarea></div>
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">💾 Save</button>
    </form>
  </div>
</div>
@endsection
