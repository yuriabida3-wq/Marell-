@extends('layouts.teacher')
@section('title', 'My Performance')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">🏆 My Performance</h1>
  <p class="text-sm text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
</div>

@if ($current)
  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    <div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-5 text-white shadow">
      <div class="text-[10px] tracking-widest text-gold font-bold">OVERALL</div>
      <div class="text-3xl font-extrabold mt-1">{{ $current->score }}/100</div>
      <div class="text-xs text-gold mt-1">Grade {{ $current->grade() }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow"><div class="text-xs text-gray-500 tracking-widest">ATTENDANCE</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $current->attendance_score }}/30</div></div>
    <div class="bg-white rounded-2xl p-5 shadow"><div class="text-xs text-gray-500 tracking-widest">MARKS ENTRY</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $current->marks_entry_score }}/20</div></div>
    <div class="bg-white rounded-2xl p-5 shadow"><div class="text-xs text-gray-500 tracking-widest">LESSON PLANS</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $current->lesson_plan_score }}/25</div></div>
    <div class="bg-white rounded-2xl p-5 shadow"><div class="text-xs text-gray-500 tracking-widest">CLASS PERF</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $current->class_performance_score }}/25</div></div>
  </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b"><div class="font-extrabold text-navy">📅 Recent Check-ins</div></div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left"><th class="p-3">DATE</th><th class="p-3">IN</th><th class="p-3">OUT</th><th class="p-3 text-center">HOURS</th></tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($checkins as $c)
          <tr>
            <td class="p-3 font-semibold text-navy">{{ $c->date->format('d M Y') }}</td>
            <td class="p-3 text-xs">{{ $c->clock_in ?? '—' }}</td>
            <td class="p-3 text-xs">{{ $c->clock_out ?? '—' }}</td>
            <td class="p-3 text-center font-bold">{{ $c->hoursWorked() }}h</td>
          </tr>
        @empty
          <tr><td colspan="4" class="p-8 text-center text-gray-400">No check-ins yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b"><div class="font-extrabold text-navy">📊 Monthly History</div></div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left"><th class="p-3">MONTH</th><th class="p-3 text-center">SCORE</th><th class="p-3 text-center">GRADE</th></tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($history as $h)
          <tr>
            <td class="p-3 font-semibold text-navy">{{ $h->month->format('M Y') }}</td>
            <td class="p-3 text-center font-bold">{{ $h->score }}/100</td>
            <td class="p-3 text-center font-bold {{ $h->gradeColor() }}">{{ $h->grade() }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="p-8 text-center text-gray-400">No history</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
