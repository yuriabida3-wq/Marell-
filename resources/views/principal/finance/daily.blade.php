@extends('layouts.admin')
@section('title', 'Daily Report')
@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div>
    <a href="{{ route('principal.finance.index') }}" class="text-xs text-gray-500 hover:text-navy">← Finance</a>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Daily Collection Report</h1>
    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
  </div>
  <form method="GET" class="flex gap-2">
    <input type="date" name="date" value="{{ $date }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm focus:border-gold focus:outline-none">
    <button class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">Apply</button>
  </form>
</div>

<div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-6 text-white shadow-lg mb-6">
  <div class="text-[10px] tracking-widest font-bold text-gold">TOTAL COLLECTION</div>
  <div class="text-3xl md:text-4xl font-extrabold mt-2">KES {{ number_format($total, 0) }}</div>
  <div class="text-xs text-white/70 mt-1">{{ $payments->count() }} transactions</div>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">TIME</th>
          <th class="p-3">RECEIPT</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3">METHOD</th>
          <th class="p-3">TXN</th>
          <th class="p-3 text-right">AMOUNT</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($payments as $p)
          <tr>
            <td class="p-3 text-xs text-gray-500">{{ $p->created_at->format('H:i') }}</td>
            <td class="p-3 font-mono text-xs text-navy">{{ $p->receipt_no }}</td>
            <td class="p-3">{{ $p->student->name ?? '—' }}</td>
            <td class="p-3">{{ $p->method }}</td>
            <td class="p-3 font-mono text-xs">{{ $p->transaction_code ?: '—' }}</td>
            <td class="p-3 text-right font-bold text-navy">KES {{ number_format($p->amount, 0) }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-gray-400 text-sm">No collection on this date</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-6 text-right text-xs text-gray-400">
  Signed: _____________________________ (Bursar)
</div>

@endsection
