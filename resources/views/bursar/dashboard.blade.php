@extends('layouts.bursar')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Bursar Dashboard</h1>
  <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
</div>

{{-- KPI CARDS --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
    <x-stat-card
        label="Today"
        :value="'KES ' . number_format($todayTotal, 0)"
        :count="$todayTotal"
        icon="banknotes"
        tint="primary"
        :caption="$todayCount . ' payments'"
    />
    <x-stat-card
        label="Defaulters"
        :value="$defaulters"
        :count="$defaulters"
        icon="exclamation-triangle"
        tint="danger"
        caption="Students with balance"
    />
    <x-stat-card
        label="Total Outstanding"
        :value="'KES ' . number_format($totalOutstanding, 0)"
        :count="$totalOutstanding"
        icon="banknotes"
        tint="warning"
        caption="Fees owed"
    />
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
