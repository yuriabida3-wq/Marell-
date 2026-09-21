@extends('layouts.admin')
@section('title', 'Results Overview')
@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">📈 Results Overview</h1>
    <p class="text-sm text-gray-500 mt-1">All exams, entry progress, top performers, class means</p>
  </div>
</div>

@if ($exams->isEmpty())
  <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded text-sm">
    ⚠️ No exams created yet. Ask the DOS to create one.
  </div>
@else

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="flex flex-col sm:flex-row gap-2">
    <select name="exam_id" class="flex-1 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      @foreach ($exams as $e)
        <option value="{{ $e->id }}" @selected($exam && $exam->id === $e->id)>
          {{ $e->name }} · {{ $e->term }} {{ $e->year }} ({{ $e->results_count }} marks) · {{ strtoupper($e->status) }}
        </option>
      @endforeach
    </select>
    <button class="px-6 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Load</button>
    @if ($exam)
      <a href="{{ route('principal.results.export', $exam) }}" class="px-6 min-h-[42px] rounded-xl bg-green-600 text-white font-semibold text-sm text-center leading-[42px]">📊 Export CSV</a>
    @endif
  </form>
</div>

@if ($exam)
  {{-- EXAM HEADER --}}
  <div class="bg-gradient-to-r from-navy to-[#082f6f] rounded-2xl p-6 text-white shadow mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <div class="text-[10px] tracking-widest font-bold text-gold">EXAM</div>
        <div class="text-2xl font-extrabold">{{ $exam->name }}</div>
        <div class="text-sm opacity-80">{{ $exam->term }} · {{ $exam->year }}</div>
      </div>
      <div class="text-right">
        @php $sc = match($exam->status) { 'published' => 'bg-green-500', 'closed' => 'bg-yellow-500', 'open' => 'bg-blue-500', default => 'bg-gray-500' }; @endphp
        <div class="inline-block px-4 py-2 rounded-full {{ $sc }} text-white font-bold text-sm">{{ strtoupper($exam->status) }}</div>
      </div>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow">
      <div class="text-[10px] text-gray-500 tracking-widest font-bold">MARKS ENTRIES</div>
      <div class="text-2xl font-extrabold text-navy mt-1">{{ number_format($exam->results_count) }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow">
      <div class="text-[10px] text-gray-500 tracking-widest font-bold">CLASSES</div>
      <div class="text-2xl font-extrabold text-navy mt-1">{{ count($classBreakdown) }}</div>
    </div>
    <div class="bg-gold rounded-2xl p-5 shadow text-navy">
      <div class="text-[10px] tracking-widest font-bold">OVERALL MEAN</div>
      <div class="text-2xl font-extrabold mt-1">
        @php
          $overallMean = collect($classBreakdown)->avg('mean') ?: 0;
          echo round($overallMean, 1);
        @endphp%
      </div>
    </div>
    <div class="bg-green-50 rounded-2xl p-5 shadow">
      <div class="text-[10px] text-gray-500 tracking-widest font-bold">STUDENTS WITH MARKS</div>
      <div class="text-2xl font-extrabold text-green-700 mt-1">{{ collect($classBreakdown)->sum('entered') }}</div>
    </div>
  </div>

  {{-- CLASS BREAKDOWN --}}
  <div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
    <div class="p-5 border-b">
      <div class="font-extrabold text-navy">📊 Class Performance</div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-3">CLASS</th>
            <th class="p-3 text-center">STUDENTS</th>
            <th class="p-3 text-center">ENTERED</th>
            <th class="p-3 text-center">PROGRESS</th>
            <th class="p-3 text-center">MEAN</th>
            <th class="p-3 text-center">HIGH</th>
            <th class="p-3 text-center">LOW</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($classBreakdown as $c)
            @php
              $pct = $c['total'] > 0 ? round(($c['entered'] / $c['total']) * 100) : 0;
              $pcolor = $pct === 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-yellow-500' : 'bg-red-500');
            @endphp
            <tr class="hover:bg-gray-50">
              <td class="p-3 font-semibold text-navy">{{ $c['class']->label() }}</td>
              <td class="p-3 text-center text-gray-600">{{ $c['total'] }}</td>
              <td class="p-3 text-center font-bold">{{ $c['entered'] }}</td>
              <td class="p-3">
                <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                  <div class="{{ $pcolor }} h-full" style="width: {{ $pct }}%"></div>
                </div>
                <div class="text-[10px] text-gray-500 mt-1">{{ $pct }}% complete</div>
              </td>
              <td class="p-3 text-center">
                <span class="font-bold {{ $c['mean'] >= 60 ? 'text-green-600' : ($c['mean'] >= 45 ? 'text-yellow-600' : 'text-red-600') }}">
                  {{ $c['mean'] }}
                </span>
              </td>
              <td class="p-3 text-center text-green-600 font-bold">{{ $c['highest'] }}</td>
              <td class="p-3 text-center text-red-600">{{ $c['lowest'] }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="p-12 text-center text-gray-400">No classes with results yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- TOP PERFORMERS --}}
  @if (count($topStudents))
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div class="p-5 border-b">
        <div class="font-extrabold text-navy">🏆 Top 10 Performers</div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-500">
            <tr class="text-left">
              <th class="p-3">#</th>
              <th class="p-3">STUDENT</th>
              <th class="p-3">CLASS</th>
              <th class="p-3 text-center">TOTAL</th>
              <th class="p-3 text-center">MEAN</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @foreach ($topStudents as $i => $t)
              <tr class="hover:bg-gray-50">
                <td class="p-3 font-bold text-navy">
                  @if ($i === 0) 🥇
                  @elseif ($i === 1) 🥈
                  @elseif ($i === 2) 🥉
                  @else {{ $i + 1 }}
                  @endif
                </td>
                <td class="p-3">
                  <div class="font-semibold text-navy">{{ $t['student']->name ?? '—' }}</div>
                  <div class="text-xs text-gray-500 font-mono">{{ $t['student']->adm_no ?? '' }}</div>
                </td>
                <td class="p-3 text-gray-600">{{ $t['student']->class ?? '' }} {{ $t['student']->stream ?? '' }}</td>
                <td class="p-3 text-center font-bold">{{ $t['total'] }}</td>
                <td class="p-3 text-center font-bold text-navy">{{ $t['mean'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
@endif
@endif
@endsection
