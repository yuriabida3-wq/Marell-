@extends('layouts.dos')
@section('title', 'Timetable')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Timetable Engine</h1>
  <p class="text-sm text-gray-500 mt-1">Auto-generate or manually manage class timetables.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  {{-- GENERATE --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">⚡ Auto-Generate by Class</h2>

    @if ($classes->isEmpty())
      <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 text-sm rounded">
        ⚠️ Create classes first — <a href="/dos/classes" class="underline font-semibold">Classes</a>
      </div>
    @else
      <form method="POST" action="{{ route('dos.timetable.generate') }}" class="space-y-4">
        @csrf

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS *</label>
          <select name="class_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
            <option value="">-- Select --</option>
            @foreach ($classes as $c)
              <option value="{{ $c->id }}">{{ $c->label() }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SUBJECTS * (comma-separated)</label>
          <textarea name="subject_list" rows="4" required placeholder="English, Kiswahili, Mathematics, Science, Social Studies, CRE, Agriculture, Creative Arts"
                    class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-sm focus:border-gold focus:outline-none">English, Kiswahili, Mathematics, Science, Social Studies, CRE, Agriculture, Creative Arts</textarea>
        </div>

        <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">
          🎯 Generate Timetable
        </button>

        <div class="text-xs text-gray-500">
          ⚙️ Greedy algorithm: assigns subjects to Mon–Fri, 8 periods/day. Avoids teacher double-booking across the school.
        </div>
      </form>
    @endif
  </div>

  {{-- VIEW BY CLASS --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">👁️ View Timetable</h2>

    @if ($classes->isEmpty())
      <p class="text-sm text-gray-500">No classes available.</p>
    @else
      <div class="space-y-2">
        @foreach ($classes as $c)
          <a href="{{ route('dos.timetable.show', $c) }}"
             class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
            <span class="font-semibold">{{ $c->label() }}</span>
            <span class="text-xs">{{ $c->studentCount() }} students →</span>
          </a>
        @endforeach
      </div>
    @endif

    <div class="mt-6 pt-4 border-t">
      <a href="{{ route('dos.timetable.teacher') }}" class="btn btn-navy w-full text-sm">👨‍🏫 View by Teacher</a>
    </div>
  </div>

</div>

@endsection
