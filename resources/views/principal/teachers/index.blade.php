@extends('layouts.admin')
@section('title', 'Teachers')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">👨‍🏫 Teachers</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $totalTeachers }} teachers · {{ $totalAssign }} assignments · {{ $totalSlots }} timetable slots</p>
  </div>
  <a href="{{ route('principal.teachers.create') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Add Teacher</a>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
  <div class="bg-navy rounded-2xl p-5 text-white shadow">
    <div class="text-[10px] tracking-widest font-bold text-gold">TOTAL TEACHERS</div>
    <div class="text-3xl font-extrabold mt-1">{{ $totalTeachers }}</div>
  </div>
  <div class="bg-gold rounded-2xl p-5 text-navy shadow">
    <div class="text-[10px] tracking-widest font-bold">SUBJECT ASSIGNMENTS</div>
    <div class="text-3xl font-extrabold mt-1">{{ $totalAssign }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-navy">TIMETABLE SLOTS</div>
    <div class="text-3xl font-extrabold text-navy mt-1">{{ $totalSlots }}</div>
  </div>
  <div class="bg-green-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-green-700">ACTIVE</div>
    <div class="text-3xl font-extrabold text-green-700 mt-1">
      {{ \App\Models\User::role('teacher')->where('active', true)->count() }}
    </div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  @forelse ($stats as $s)
    <div class="bg-white rounded-2xl p-5 shadow hover:shadow-xl transition">
      <div class="flex items-start gap-4 mb-4">
        <div class="w-14 h-14 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xl flex-shrink-0">
          {{ strtoupper(substr($s['teacher']->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-navy truncate">{{ $s['teacher']->name }}</div>
          <div class="text-xs text-gray-500 truncate">{{ $s['teacher']->email }}</div>
          <div class="text-xs font-mono text-gray-400 mt-0.5">{{ $s['teacher']->phone ?? '—' }}</div>
        </div>
        @if ($s['teacher']->active)
          <span class="text-xs font-bold text-green-600">●</span>
        @else
          <span class="text-xs font-bold text-red-600">●</span>
        @endif
      </div>

      <div class="grid grid-cols-3 gap-2 text-center text-xs">
        <div class="bg-gray-50 rounded-lg p-2">
          <div class="text-gray-500">Classes</div>
          <div class="font-bold text-navy">{{ $s['classes'] }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-2">
          <div class="text-gray-500">Subjects</div>
          <div class="font-bold text-navy">{{ $s['subjects'] }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-2">
          <div class="text-gray-500">Slots</div>
          <div class="font-bold text-navy">{{ $s['slots'] }}</div>
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <a href="{{ route('principal.teachers.show', $s['teacher']) }}" class="flex-1 text-center px-3 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:scale-105 transition">View</a>
        <a href="{{ route('principal.teachers.edit', $s['teacher']) }}" class="flex-1 text-center px-3 py-2 rounded-xl bg-gray-100 text-navy text-xs font-semibold hover:scale-105 transition">Edit</a>
      </div>
    </div>
  @empty
    <div class="md:col-span-3 bg-white rounded-2xl p-12 text-center text-gray-400 shadow">
      <div class="text-4xl mb-2">👨‍🏫</div>
      No teachers registered yet
    </div>
  @endforelse
</div>
@endsection
