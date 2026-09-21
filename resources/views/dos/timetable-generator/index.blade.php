@extends('layouts.dos')
@section('title', 'Generate Timetable')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">School-Wide Timetable Generator</h1>
  <p class="text-sm text-gray-500 mt-1">One click → timetable for every class, every stream. Zero teacher overlap guaranteed.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">⚡ Generate for Whole School</h2>

    <div class="bg-gold/10 border border-gold rounded-xl p-4 mb-6 text-sm text-navy">
      <strong>How it works:</strong>
      <ol class="list-decimal list-inside mt-2 space-y-1">
        <li>Reads all Teacher Subject assignments</li>
        <li>For each class: fills 5 days × 8 periods = 40 slots</li>
        <li>Picks teacher only if free at that slot (school-wide)</li>
        <li>Guarantees no teacher is in 2 classes at once</li>
      </ol>
    </div>

    <form method="POST" action="{{ route('dos.timetable-generator.generate') }}" class="space-y-4" onsubmit="return confirm('This will REPLACE all timetables for the selected term. Continue?')">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TERM *</label>
        <select name="term" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          @foreach ($terms as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">YEAR *</label>
        <select name="year" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          @foreach ($years as $y)
            <option value="{{ $y }}">{{ $y }}</option>
          @endforeach
        </select>
      </div>

      <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">
        🎯 Generate for ALL Classes
      </button>
    </form>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">🔍 Verify & Load Report</h2>
    <p class="text-sm text-gray-600 mb-4">Check teacher clashes and per-teacher load for any term.</p>

    <form method="POST" action="{{ route('dos.timetable-generator.verify') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TERM</label>
        <select name="term" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          @foreach ($terms as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">YEAR</label>
        <select name="year" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          @foreach ($years as $y)
            <option value="{{ $y }}">{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <button class="w-full min-h-[52px] rounded-xl bg-navy text-white font-bold hover:scale-[1.02] transition">
        📊 Run Verification
      </button>
    </form>
  </div>
</div>

<div class="mt-6 bg-blue-50 border-l-4 border-navy rounded-xl p-4 text-sm text-navy">
  <strong>Best practice:</strong> Assign teacher subjects first at <a href="{{ route('dos.teacher-subjects.index') }}" class="underline font-semibold">Teacher Subjects</a>, then generate.
  Without assignments, generator falls back to generic subjects with no teacher.
</div>

@endsection
