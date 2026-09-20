@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}. Here's your school at a glance.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('principal.export-students') }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:scale-105 transition">📊 Export Excel</a>
    <a href="/principal/sms" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold hover:scale-105 transition">📱 Send SMS</a>
  </div>
</div>

{{-- KPI CARDS --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-5 shadow-lg text-white">
    <div class="text-[10px] tracking-widest font-bold text-gold">TODAY'S COLLECTION</div>
    <div class="text-2xl md:text-3xl font-extrabold mt-2">KES {{ number_format($todayCollection, 0) }}</div>
    <div class="text-xs text-white/70 mt-1">
      Yesterday: KES {{ number_format($yesterdayCollection, 0) }}
      @if ($yesterdayCollection > 0)
        @php $delta = round((($todayCollection - $yesterdayCollection) / max(1, $yesterdayCollection)) * 100, 1); @endphp
        <span class="{{ $delta >= 0 ? 'text-green-300' : 'text-red-300' }}">{{ $delta >= 0 ? '↑' : '↓' }} {{ abs($delta) }}%</span>
      @endif
    </div>
  </div>

  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-red-600">FEE DEFAULTERS</div>
    <div class="text-2xl md:text-3xl font-extrabold text-red-600 mt-2">{{ $defaulters }}</div>
    <div class="text-xs text-gray-500 mt-1">Students with balance</div>
  </div>

  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-navy">ACTIVE STUDENTS</div>
    <div class="text-2xl md:text-3xl font-extrabold text-navy mt-2">{{ $activeStudents }}</div>
    <div class="text-xs text-gray-500 mt-1">Currently enrolled</div>
  </div>

  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-gold">TEACHERS</div>
    <div class="text-2xl md:text-3xl font-extrabold text-navy mt-2">{{ $teachers }}</div>
    <div class="text-xs text-gray-500 mt-1">On payroll</div>
  </div>
</div>

{{-- CHARTS ROW --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
  {{-- FEES TREND --}}
  <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-extrabold text-navy">💹 Fees Trend — Last 7 Days</h2>
      <span class="text-xs text-gray-500">KES</span>
    </div>
    <div style="height: 260px;">
      <canvas id="feesTrend"></canvas>
    </div>
  </div>

  {{-- CLASS DISTRIBUTION --}}
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-extrabold text-navy">🎓 Class Distribution</h2>
    </div>
    <div style="height: 260px;">
      <canvas id="classDist"></canvas>
    </div>
  </div>
</div>

{{-- RECENT PAYMENTS --}}
<div class="mt-6 bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b flex items-center justify-between">
    <h2 class="font-extrabold text-navy">💳 Recent Payments</h2>
    <a href="/principal/finance" class="text-xs text-navy hover:text-gold font-semibold">View all →</a>
  </div>

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
                <a href="{{ URL::signedRoute('pay.receipt', ['payment' => $p->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">📄 Receipt</a>
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
</div>

{{-- QUICK LINKS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
  <a href="/principal/students" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">🎓</div>
    <div class="font-bold text-navy text-sm">Manage Students</div>
  </a>
  <a href="/principal/finance" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">💰</div>
    <div class="font-bold text-navy text-sm">Finance</div>
  </a>
  <a href="/principal/defaulters" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">⚠️</div>
    <div class="font-bold text-navy text-sm">Defaulters</div>
  </a>
  <a href="/principal/sms" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">📱</div>
    <div class="font-bold text-navy text-sm">SMS Center</div>
  </a>
</div>

@push('scripts')
<script>
// FEES TREND (Line)
new Chart(document.getElementById('feesTrend'), {
  type: 'line',
  data: {
    labels: @json($labels),
    datasets: [{
      label: 'Collection (KES)',
      data: @json($series),
      borderColor: '#0B3D91',
      backgroundColor: 'rgba(11,61,145,0.1)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#D4AF37',
      pointRadius: 5,
      pointHoverRadius: 8,
      borderWidth: 3,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => 'KES ' + Number(ctx.parsed.y).toLocaleString()
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: (v) => 'KES ' + Number(v).toLocaleString(), font: { size: 10 } },
        grid: { color: '#f1f5f9' }
      },
      x: { grid: { display: false }, ticks: { font: { size: 10 } } }
    }
  }
});

// CLASS DISTRIBUTION (Doughnut)
new Chart(document.getElementById('classDist'), {
  type: 'doughnut',
  data: {
    labels: @json($classDist->pluck('class')),
    datasets: [{
      data: @json($classDist->pluck('total')),
      backgroundColor: ['#0B3D91','#D4AF37','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#6b7280'],
      borderWidth: 0,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12, padding: 8 } }
    },
    cutout: '60%'
  }
});
</script>
@endpush

@endsection
