@extends('layouts.bursar')
@section('title', 'Daily Report')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Daily Collection</h1>
    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
  </div>
  <form method="GET" class="flex gap-2">
    <input type="date" name="date" value="{{ $date }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
    <button class="px-4 rounded-xl bg-navy text-white text-sm font-semibold">Go</button>
  </form>
</div>

<div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-6 text-white shadow-lg mb-4">
  <div class="text-[10px] tracking-widest font-bold text-gold">TOTAL COLLECTION</div>
  <div class="text-3xl md:text-4xl font-extrabold mt-2">KES {{ number_format($total, 0) }}</div>
  <div class="text-xs opacity-70 mt-1">{{ $payments->count() }} transactions</div>

  @if ($byMethod->count())
    <div class="mt-4 pt-4 border-t border-white/20 grid grid-cols-3 gap-2 text-center">
      @foreach ($byMethod as $m => $amt)
        <div>
          <div class="text-[10px] opacity-70">{{ $m }}</div>
          <div class="font-bold text-sm">KES {{ number_format($amt, 0) }}</div>
        </div>
      @endforeach
    </div>
  @endif
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs md:text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">TIME</th>
          <th class="p-3">RECEIPT</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3">METHOD</th>
          <th class="p-3 text-right">AMOUNT</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($payments as $p)
          <tr>
            <td class="p-3 text-gray-500">{{ $p->created_at->format('H:i') }}</td>
            <td class="p-3 font-mono text-navy">{{ $p->receipt_no }}</td>
            <td class="p-3">{{ $p->student->name ?? '—' }}</td>
            <td class="p-3">{{ $p->method }}</td>
            <td class="p-3 text-right font-bold text-navy">KES {{ number_format($p->amount, 0) }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-12 text-center text-gray-400">No collection on this date</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
