@extends('layouts.dos')
@section('title', 'Timetable Verification')
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.timetable-generator.index') }}" class="text-xs text-gray-500">← Generator</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Verification · {{ $term }} {{ $year }}</h1>
</div>

{{-- CLASHES --}}
<div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
  <div class="p-5 border-b flex items-center gap-3">
    <div class="w-10 h-10 rounded-full {{ count($clashes) === 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center text-xl">
      {{ count($clashes) === 0 ? '✓' : '!' }}
    </div>
    <div>
      <div class="font-extrabold text-navy text-lg">Teacher Clash Check</div>
      <div class="text-xs text-gray-500">{{ count($clashes) === 0 ? 'No double-booking detected' : count($clashes) . ' clashes found' }}</div>
    </div>
  </div>

  @if (count($clashes) === 0)
    <div class="p-8 text-center text-green-700 bg-green-50">
      <div class="text-3xl mb-2">✅</div>
      <div class="font-bold">Perfect — no teacher is scheduled in 2 places at once.</div>
    </div>
  @else
    <table class="w-full text-sm">
      <thead class="bg-red-50 text-xs text-red-700">
        <tr class="text-left">
          <th class="p-3">TEACHER ID</th>
          <th class="p-3">SLOT</th>
          <th class="p-3">CLASS A</th>
          <th class="p-3">CLASS B</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($clashes as $c)
          <tr>
            <td class="p-3 font-mono">{{ $c['teacher_id'] }}</td>
            <td class="p-3 font-bold text-red-700">{{ $c['slot'] }}</td>
            <td class="p-3">{{ $c['class_a'] }}</td>
            <td class="p-3">{{ $c['class_b'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

{{-- TEACHER LOAD --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy text-lg">📊 Teacher Workload</div>
    <div class="text-xs text-gray-500">Slots assigned per teacher for this term</div>
  </div>
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">TEACHER</th>
        <th class="p-3 text-center">SLOTS / WEEK</th>
        <th class="p-3">LOAD</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($load as $t)
        @php
          $max = collect($load)->max('slots') ?: 1;
          $pct = round(($t['slots'] / $max) * 100);
          $color = $t['slots'] > 32 ? 'bg-red-500' : ($t['slots'] > 24 ? 'bg-yellow-500' : 'bg-green-500');
        @endphp
        <tr>
          <td class="p-3 font-semibold text-navy">{{ $t['name'] }}</td>
          <td class="p-3 text-center font-bold">{{ $t['slots'] }}</td>
          <td class="p-3">
            <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="{{ $color }} h-full" style="width: {{ $pct }}%"></div>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="p-8 text-center text-gray-400">No teachers assigned yet</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection
