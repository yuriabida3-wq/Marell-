@extends('layouts.admin')
@section('title', 'Attendance')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">✅ Attendance</h1>
    <p class="text-sm text-gray-500 mt-1">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('principal.attendance.chronic') }}" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold">⚠️ Chronic Absentees</a>
    <a href="{{ route('principal.attendance.export', ['date' => $date]) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold">📊 Export CSV</a>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

{{-- FILTER --}}
<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="flex flex-wrap gap-2">
    <input type="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
    <select name="class" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">All Classes</option>
      @foreach ($classes as $c)
        <option value="{{ $c }}" @selected($class === $c)>{{ $c }}</option>
      @endforeach
    </select>
    <button class="px-6 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Apply</button>
  </form>
</div>

{{-- KPI CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
  <div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-5 text-white shadow">
    <div class="text-[10px] tracking-widest text-gold font-bold">ATTENDANCE RATE</div>
    <div class="text-3xl font-extrabold mt-1">{{ $rate }}%</div>
  </div>
  <div class="bg-green-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest text-green-700 font-bold">PRESENT</div>
    <div class="text-3xl font-extrabold text-green-700 mt-1">{{ $present }}</div>
  </div>
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest text-red-700 font-bold">ABSENT</div>
    <div class="text-3xl font-extrabold text-red-600 mt-1">{{ $absent }}</div>
  </div>
  <div class="bg-yellow-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest text-yellow-700 font-bold">LATE</div>
    <div class="text-3xl font-extrabold text-yellow-600 mt-1">{{ $late }}</div>
  </div>
  <div class="bg-gray-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest text-gray-600 font-bold">UNMARKED</div>
    <div class="text-3xl font-extrabold text-gray-600 mt-1">{{ $unmarked }}</div>
  </div>
</div>

{{-- CLASS BREAKDOWN --}}
<div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy">📊 By Class</div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">CLASS</th>
          <th class="p-3 text-center">TOTAL</th>
          <th class="p-3 text-center">MARKED</th>
          <th class="p-3 text-center">PRESENT</th>
          <th class="p-3 text-center">UNMARKED</th>
          <th class="p-3">PROGRESS</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($byClass as $row)
          @php
            $pct = $row['total'] > 0 ? round(($row['marked'] / $row['total']) * 100) : 0;
            $color = $pct === 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-yellow-500' : 'bg-red-500');
          @endphp
          <tr>
            <td class="p-3 font-semibold text-navy">{{ $row['class'] }}</td>
            <td class="p-3 text-center text-gray-600">{{ $row['total'] }}</td>
            <td class="p-3 text-center font-bold">{{ $row['marked'] }}</td>
            <td class="p-3 text-center text-green-700 font-bold">{{ $row['present'] }}</td>
            <td class="p-3 text-center {{ $row['unmarked'] > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">{{ $row['unmarked'] }}</td>
            <td class="p-3">
              <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                <div class="{{ $color }} h-full" style="width: {{ $pct }}%"></div>
              </div>
              <div class="text-[10px] text-gray-500 mt-1">{{ $pct }}%</div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- DETAILED LIST --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy">📋 Detailed Records</div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">TIME</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3">CLASS</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3">REASON</th>
          <th class="p-3">MARKED BY</th>
          <th class="p-3 text-center">SMS</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($attendances as $a)
          <tr class="hover:bg-gray-50">
            <td class="p-3 text-xs text-gray-500">{{ $a->created_at->format('H:i') }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy text-sm">{{ $a->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500 font-mono">{{ $a->student->adm_no ?? '' }}</div>
            </td>
            <td class="p-3 text-gray-600">{{ $a->student->class ?? '' }}</td>
            <td class="p-3 text-center">
              <span class="px-2 py-1 rounded-full text-xs font-bold {{ $a->statusColor() }}">
                {{ $a->statusIcon() }} {{ strtoupper($a->status) }}
              </span>
            </td>
            <td class="p-3 text-xs text-gray-600">{{ $a->reason ?? '—' }}</td>
            <td class="p-3 text-xs text-gray-600">{{ $a->marked_by_name ?? '—' }}</td>
            <td class="p-3 text-center">
              @if ($a->sms_sent)
                <span class="text-xs text-green-600 font-bold">✓ Sent</span>
              @else
                <span class="text-xs text-gray-400">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="p-12 text-center text-gray-400">No attendance records for this date</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $attendances->links() }}</div>
@endsection
