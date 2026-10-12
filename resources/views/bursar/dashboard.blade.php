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
    @php
    $bursarActions = [
        ['label' => 'Find Student', 'href' => '/bursar/students', 'icon' => 'magnifying-glass', 'tint' => 'primary'],
        ['label' => 'Record',       'href' => '/bursar/record',   'icon' => 'pencil-square',    'tint' => 'accent'],
        ['label' => 'Balances',     'href' => '/bursar/balances', 'icon' => 'chart-bar',        'tint' => 'info'],
        ['label' => 'Daily',        'href' => '/bursar/daily',    'icon' => 'calendar',         'tint' => 'success'],
    ];
    @endphp

    <x-card title="Quick Actions">
        <x-quick-links :items="$bursarActions" columns="2" />
    </x-card>

    <x-card title="Recent" action="View all" action-href="/bursar/payments">
        @if ($recent->count())
            <div class="space-y-2">
                @foreach ($recent as $p)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-surface-2">
                        <div>
                            <div class="text-sm font-semibold text-bodytext">{{ $p->student->name ?? '—' }}</div>
                            <div class="text-xs text-mutedtext">{{ $p->receipt_no }} · {{ $p->created_at->format('d M H:i') }}</div>
                        </div>
                        <div class="text-sm font-bold text-success">KES {{ number_format($p->amount, 0) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-mutedtext text-center py-6">No payments yet</p>
        @endif
    </x-card>
</div>

@endsection
