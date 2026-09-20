@extends('layouts.admin')
@section('title', 'Finance')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Finance</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $countSum }} payments · Completed: KES {{ number_format($totalSum, 0) }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('principal.finance.record') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Record Payment</a>
    <a href="{{ route('principal.finance.daily') }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold hover:scale-105 transition">📅 Daily Report</a>
    <a href="{{ route('principal.finance.export') }}?{{ http_build_query(request()->only(['q','method','status','from','to'])) }}"
       class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:scale-105 transition">📊 Export</a>
  </div>
</div>

{{-- FILTERS --}}
<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
    <input name="q" value="{{ $q }}" placeholder="🔍 Search txn, receipt, name, ADM..."
           class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">

    <select name="method" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <option value="">All Methods</option>
      <option value="M-Pesa" @selected($method === 'M-Pesa')>M-Pesa</option>
      <option value="Cash"   @selected($method === 'Cash')>Cash</option>
      <option value="Bank"   @selected($method === 'Bank')>Bank</option>
    </select>

    <select name="status" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <option value="">All Statuses</option>
      <option value="completed" @selected($status === 'completed')>Completed</option>
      <option value="pending"   @selected($status === 'pending')>Pending</option>
      <option value="failed"    @selected($status === 'failed')>Failed</option>
    </select>

    <div class="grid grid-cols-2 gap-2">
      <input type="date" name="from" value="{{ $from }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-xs focus:border-gold focus:outline-none">
      <input type="date" name="to"   value="{{ $to }}"   class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-xs focus:border-gold focus:outline-none">
    </div>

    <button class="md:col-span-5 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm hover:scale-[1.02] transition">Apply Filters</button>
  </form>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">DATE</th>
          <th class="p-3">RECEIPT</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3">METHOD</th>
          <th class="p-3">TXN CODE</th>
          <th class="p-3 text-right">AMOUNT</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">RECEIPT</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($payments as $p)
          <tr class="hover:bg-gray-50">
            <td class="p-3 text-gray-500 text-xs">{{ $p->created_at->format('d M Y H:i') }}</td>
            <td class="p-3 font-mono text-xs text-navy">{{ $p->receipt_no ?: '—' }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy">{{ $p->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500">{{ $p->student->adm_no ?? '' }} · {{ $p->student->class ?? '' }}</div>
            </td>
            <td class="p-3">{{ $p->method }}</td>
            <td class="p-3 font-mono text-xs">{{ $p->transaction_code ?: '—' }}</td>
            <td class="p-3 text-right font-bold text-navy">KES {{ number_format($p->amount, 0) }}</td>
            <td class="p-3 text-center">
              @php
                $cls = match($p->status) {
                  'completed' => 'bg-green-100 text-green-700',
                  'pending'   => 'bg-yellow-100 text-yellow-700',
                  default     => 'bg-red-100 text-red-700',
                };
              @endphp
              <span class="px-2 py-1 rounded-full text-xs font-bold {{ $cls }}">{{ ucfirst($p->status) }}</span>
            </td>
            <td class="p-3 text-right">
              @if ($p->status === 'completed')
                <a href="{{ URL::signedRoute('pay.receipt', ['payment' => $p->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">📄 PDF</a>
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-12 text-center text-gray-400 text-sm">No payments</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $payments->links() }}</div>

@endsection
