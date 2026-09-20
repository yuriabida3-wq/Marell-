@extends('layouts.dos')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Academic Dashboard</h1>
  <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}. Here's your academic overview.</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-5 shadow-lg text-white">
    <div class="text-[10px] tracking-widest font-bold text-gold">ACTIVE STUDENTS</div>
    <div class="text-2xl md:text-3xl font-extrabold mt-2">{{ $stats['students'] }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-navy">TEACHERS</div>
    <div class="text-2xl md:text-3xl font-extrabold text-navy mt-2">{{ $stats['teachers'] }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-gold">CLASSES</div>
    <div class="text-2xl md:text-3xl font-extrabold text-navy mt-2">{{ $stats['classes'] }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-red-600">OPEN EXAMS</div>
    <div class="text-2xl md:text-3xl font-extrabold text-red-600 mt-2">{{ $stats['exams_open'] }}</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
  <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">🎓 Teachers</h2>
    @if ($teachers->count())
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @foreach ($teachers as $t)
          <div class="p-3 rounded-xl bg-gray-50 text-center">
            <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center font-bold mx-auto mb-2">
              {{ strtoupper(substr($t->name, 0, 1)) }}
            </div>
            <div class="text-sm font-semibold text-navy">{{ $t->name }}</div>
            <div class="text-xs text-gray-500">{{ $t->email }}</div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-8 text-gray-400 text-sm">
        <div class="text-3xl mb-2">👨‍🏫</div>
        No teachers yet — add them from <a href="/dos/teachers" class="text-navy underline">Teachers</a>
      </div>
    @endif
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">⚡ Quick Actions</h2>
    <div class="space-y-2">
      <a href="/dos/classes" class="block p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
        🏫 <span class="font-semibold">Manage Classes</span>
      </a>
      <a href="/dos/timetable" class="block p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
        📅 <span class="font-semibold">Generate Timetable</span>
      </a>
      <a href="/dos/exams" class="block p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
        📝 <span class="font-semibold">Create Exam</span>
      </a>
      <a href="/dos/students" class="block p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
        🎓 <span class="font-semibold">Register Student</span>
      </a>
    </div>
  </div>
</div>

@endsection
