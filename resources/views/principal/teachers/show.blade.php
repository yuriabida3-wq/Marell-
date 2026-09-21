@extends('layouts.admin')
@section('title', $teacher->name)
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.teachers.index') }}" class="text-xs text-gray-500">← Teachers</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $teacher->name }}</h1>
  <p class="text-sm text-gray-500">{{ $teacher->email }} · {{ $teacher->phone ?? 'No phone' }}</p>
</div>

<div class="flex flex-wrap gap-2 mb-4">
  <a href="{{ route('principal.teachers.edit', $teacher) }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">✏️ Edit</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">CLASSES ASSIGNED</div>
    <div class="text-2xl font-extrabold text-navy mt-1">{{ $myClasses->count() }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">SUBJECTS TAUGHT</div>
    <div class="text-2xl font-extrabold text-navy mt-1">{{ $subjects->unique('subject')->count() }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">TIMETABLE SLOTS</div>
    <div class="text-2xl font-extrabold text-navy mt-1">{{ $timetable->count() }}</div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  {{-- SUBJECT ASSIGNMENTS --}}
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b">
      <div class="font-extrabold text-navy">📋 Subject Assignments</div>
    </div>
    @if ($subjects->count())
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-3">SUBJECT</th>
            <th class="p-3">CLASS</th>
            <th class="p-3 text-center">PERIODS/WK</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach ($subjects as $s)
            <tr>
              <td class="p-3 font-semibold text-navy">{{ $s->subject }}</td>
              <td class="p-3 text-gray-600">{{ $s->class }} {{ $s->stream }}</td>
              <td class="p-3 text-center">{{ $s->periods_per_week }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="p-8 text-center text-gray-400 text-sm">No subject assignments yet</div>
    @endif
  </div>

  {{-- CLASSES --}}
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b">
      <div class="font-extrabold text-navy">🎓 Classes</div>
    </div>
    @if ($myClasses->count())
      <div class="p-4 space-y-2">
        @foreach ($myClasses as $c)
          <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
            <div class="font-semibold text-navy">{{ $c['class'] }} {{ $c['stream'] }}</div>
            <div class="text-xs text-gray-500">{{ $c['count'] }} students</div>
          </div>
        @endforeach
      </div>
    @else
      <div class="p-8 text-center text-gray-400 text-sm">No classes assigned</div>
    @endif
  </div>
</div>

{{-- TIMETABLE --}}
@if ($timetable->count())
<div class="bg-white rounded-2xl shadow overflow-hidden mt-6">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy">📅 Weekly Timetable ({{ $timetable->count() }} slots)</div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-xs">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-2">DAY</th>
          <th class="p-2">P</th>
          <th class="p-2">TIME</th>
          <th class="p-2">SUBJECT</th>
          <th class="p-2">CLASS</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($timetable as $t)
          <tr>
            <td class="p-2 font-semibold">{{ $t->day }}</td>
            <td class="p-2">P{{ $t->period }}</td>
            <td class="p-2 text-gray-500">{{ $t->start_time }}-{{ $t->end_time }}</td>
            <td class="p-2 font-semibold text-navy">{{ $t->subject }}</td>
            <td class="p-2 text-gray-600">{{ $t->class }} {{ $t->stream }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
