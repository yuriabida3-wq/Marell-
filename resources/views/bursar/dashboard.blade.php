@extends('layouts.bursar')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Bursar Dashboard</h1>
  <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
  <div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-5 text-white shadow-lg">
    <div class="text-[10px] tracking-widest font-bold text-gold">TODAY</div>
    <div class="text-2xl md:text-3xl font-extrabold mt-2">KES {{ number_format($todayTotal, 0) }}</div>
    <div class="text-xs text-white/70 mt-1">{{ $todayCount }} payments</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-red-600">DEFAULTERS</div>
    <div class="text-2xl md:text-3xl font-extrabold text-red-600 mt-2">{{ $defaulters }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow col-span-2">
    <div class="text-[10px] tracking-widest font-bold text-gold">TOTAL OUTSTANDING</div>
    <div class="text-2xl md:text-3xl font-extrabold text-navy mt-2">KES {{ number_format($totalOutstanding, 0) }}</div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">⚡ Quick Actions</h2>
    <div class="grid grid-cols-2 gap-3">
      <a href="/bursar/students" class="quick-btn bg-navy text-white text-sm">🔍 Find Student</a>
      <a href="/bursar/record" class="quick-btn bg-gold text-navy text-sm">➕ Record</a>
      <a href="/bursar/balances" class="quick-btn bg-gray-100 text-navy text-sm">📊 Balances</a>
      <a href="/bursar/daily" class="quick-btn bg-gray-100 text-navy text-sm">📅 Daily</a>
    </div>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-extrabold text-navy">💳 Recent</h2>
      <a href="/bursar/payments" class="text-xs text-navy hover:text-gold font-semibold">View all →</a>
    </div>
    @if ($recent->count())
      <div class="space-y-2">
        @foreach ($recent as $p)
          <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50">
            <div>
              <div class="text-sm font-semibold text-navy">{{ $p->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500">{{ $p->receipt_no }} · {{ $p->created_at->format('d M H:i') }}</div>
            </div>
            <div class="text-sm font-bold text-green-700">KES {{ number_format($p->amount, 0) }}</div>
          </div>
        @endforeach
      </div>
    @else
      <p class="text-sm text-gray-400 text-center py-6">No payments yet</p>
    @endif
  </div>
</div>

@endsection
