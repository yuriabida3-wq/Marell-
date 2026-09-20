@extends('layouts.bursar')
@section('title', 'All Payments')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">All Payments</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $payments->total() }} records · Total KES {{ number_format($total, 0) }}</p>
  </div>
</div>

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-2">
    <input name="q" value="{{ $q }}" placeholder="Search name, ADM, txn, receipt" class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
    <select name="method" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">All Methods</option>
      <option value="M-Pesa" @selected($method==='M-Pesa')>M-Pesa</option>
      <option value="Cash" @selected($method==='Cash')>Cash</option>
      <option value="Bank" @selected($method==='Bank')>Bank</option>
    </select>
    <button class="min-h-[42px] rounded-xl bg-navy text-white text-sm font-semibold">Apply</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs md:text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">DATE</th>
          <th class="p-3">RECEIPT</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3">METHOD</th>
          <th class="p-3 text-right">AMOUNT</th>
          <th class="p-3 text-right">PDF</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($payments as $p)
          <tr class="hover:bg-gray-50">
            <td class="p-3 text-gray-500">{{ $p->created_at->format('d M H:i') }}</td>
            <td class="p-3 font-mono text-navy">{{ $p->receipt_no }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy">{{ $p->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500">{{ $p->student->adm_no ?? '' }}</div>
            </td>
            <td class="p-3">{{ $p->method }}</td>
            <td class="p-3 text-right font-bold text-navy">KES {{ number_format($p->amount, 0) }}</td>
            <td class="p-3 text-right"><a href="{{ URL::signedRoute('pay.receipt', ['payment' => $p->id]) }}" class="text-navy hover:text-gold font-semibold">📄</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-12 text-center text-gray-400">No payments</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $payments->links() }}</div>

@endsection
