@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}. Here's your school at a glance.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <x-button variant="outline" size="sm" icon="document-arrow-down" href="{{ route('principal.export-students') }}">Export Excel</x-button>
    <x-button variant="navy" size="sm" icon="megaphone" href="/principal/sms">Send SMS</x-button>
  </div>
</div>

{{-- KPI CARDS --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat-card
        label="Today's Collection"
        :value="'KES ' . number_format($todayCollection, 0)"
        :count="$todayCollection"
        icon="banknotes"
        tint="primary"
        :caption="'Yesterday: KES ' . number_format($yesterdayCollection, 0)"
        :trend="$yesterdayCollection > 0 ? ($todayCollection >= $yesterdayCollection ? 'up' : 'down') : null"
        :trendValue="$yesterdayCollection > 0 ? abs(round((($todayCollection - $yesterdayCollection) / max(1, $yesterdayCollection)) * 100, 1)) . '%' : null"
    />

    <x-stat-card
        label="Fee Defaulters"
        :value="$defaulters"
        :count="$defaulters"
        icon="exclamation-triangle"
        tint="danger"
        caption="Students with balance"
    />

    <x-stat-card
        label="Active Students"
        :value="$activeStudents"
        :count="$activeStudents"
        icon="users"
        tint="primary"
        caption="Currently enrolled"
    />

    <x-stat-card
        label="Teachers"
        :value="$teachers"
        :count="$teachers"
        icon="book-open"
        tint="accent"
        caption="On payroll"
    />
</div>

{{-- CHARTS ROW --}}
@php
$feesDatasets = [[
    'label' => 'Collection (KES)',
    'data' => $series,
    'tension' => 0.4,
    'fill' => true,
    'borderWidth' => 3,
    'pointRadius' => 5,
    'pointHoverRadius' => 8,
]];
$classDatasets = [[
    'data' => $classDist->pluck('total')->all(),
    'borderWidth' => 0,
]];
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
    <x-card title="Fees Trend — Last 7 Days" subtitle="KES" padding="md" class="lg:col-span-2">
        <x-chart id="feesTrend" type="line" :labels="$labels" :datasets="$feesDatasets">
            <x-slot:script>
var c = window.skuliChartColors();
var ds = cfg.data.datasets[0];
ds.borderColor = c.primary;
ds.backgroundColor = c.primary.replace('rgb(', 'rgba(').replace(')', ', 0.1)');
ds.pointBackgroundColor = c.accent;
cfg.options.plugins = cfg.options.plugins || {};
cfg.options.plugins.legend = { display: false };
cfg.options.plugins.tooltip = {
    callbacks: {
        label: function (ctx) { return 'KES ' + Number(ctx.parsed.y).toLocaleString(); }
    }
};
cfg.options.scales = {
    y: {
        beginAtZero: true,
        ticks: { callback: function (v) { return 'KES ' + Number(v).toLocaleString(); }, font: { size: 10 } },
        grid: { color: c.border }
    },
    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
};
            </x-slot:script>
        </x-chart>
    </x-card>

    <x-card title="Class Distribution" padding="md">
        <x-chart id="classDist" type="doughnut" :labels="$classDist->pluck('class')->all()" :datasets="$classDatasets">
            <x-slot:script>
var c = window.skuliChartColors();
cfg.data.datasets[0].backgroundColor = [c.primary, c.accent, c.info, c.success, c.warning, c.danger, c.primaryDark, c.muted, c.text, c.border];
cfg.options.plugins = cfg.options.plugins || {};
cfg.options.plugins.legend = { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12, padding: 8 } };
cfg.options.cutout = '60%';
            </x-slot:script>
        </x-chart>
    </x-card>
</div>

{{-- RECENT PAYMENTS --}}
<x-card title="Recent Payments" action="View all" action-href="/principal/finance" padding="none" class="mt-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 tracking-widest">
                    <th class="p-3">PAYMENT ID</th>
                    <th class="p-3">STUDENT</th>
                    <th class="p-3">CLASS</th>
                    <th class="p-3 text-right">AMOUNT</th>
                    <th class="p-3">DATE</th>
                    <th class="p-3 text-center">STATUS</th>
                    <th class="p-3 text-right">ACTION</th>
                </tr>
            </thead>
            <tbody class="divide-y">
        @forelse ($recentPayments as $p)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-mono text-xs">#{{ $p->id }}</td>
            <td class="p-3 font-semibold text-navy">{{ $p->student->name ?? '—' }}</td>
            <td class="p-3 text-gray-600">{{ $p->student->class ?? '—' }}</td>
            <td class="p-3 text-right font-bold text-navy">KES {{ number_format($p->amount, 0) }}</td>
            <td class="p-3 text-gray-500 text-xs">{{ $p->created_at->format('d M Y H:i') }}</td>
            <td class="p-3 text-center">
              @php
                $badgeVariant = match($p->status) {
                  'completed' => 'success',
                  'pending'   => 'warning',
                  default     => 'danger',
                };
              @endphp
              <x-badge :variant="$badgeVariant">{{ ucfirst($p->status) }}</x-badge>
            </td>
            <td class="p-3 text-right">
              @if ($p->status === 'completed')
                <a href="{{ URL::signedRoute('pay.receipt', ['payment' => $p->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">Receipt</a>
              @elseif ($p->status === 'pending')
                <button class="text-xs font-semibold text-gray-500">Resend SMS</button>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="p-8 text-center text-gray-400 text-sm">No payments yet</td></tr>
        @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- QUICK LINKS --}}
@php
$tiles = [
    ['label' => 'Manage Students', 'href' => '/principal/students',           'icon' => 'users',                 'tint' => 'primary'],
    ['label' => 'Finance',         'href' => '/principal/finance',            'icon' => 'banknotes',             'tint' => 'success'],
    ['label' => 'Defaulters',      'href' => '/principal/finance/defaulters', 'icon' => 'exclamation-triangle',  'tint' => 'danger'],
    ['label' => 'SMS Center',      'href' => '/principal/sms',                'icon' => 'megaphone',             'tint' => 'info'],
];
@endphp
<x-quick-links :items="$tiles" class="mt-6" />

@endsection
