@extends('layouts.teacher')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}</h1>
  <p class="text-sm text-gray-500 mt-1">Today is {{ now()->format('l, d M Y') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

  {{-- TODAY'S SLOTS --}}
  <div class="bg-white rounded-2xl p-5 shadow">
    <h2 class="font-extrabold text-navy mb-4">📅 Today's Classes ({{ $today }})</h2>
    @if ($todaySlots->count())
      <div class="space-y-2">
        @foreach ($todaySlots as $s)
          <div class="p-3 rounded-xl bg-gray-50 border-l-4 border-gold">
            <div class="flex justify-between items-center">
              <div>
                <div class="font-bold text-navy text-sm">P{{ $s->period }} · {{ $s->subject }}</div>
                <div class="text-xs text-gray-500">{{ $s->class }} {{ $s->stream }} · {{ $s->start_time }}–{{ $s->end_time }}</div>
              </div>
              <div class="text-xs text-gray-400">{{ $s->day }}</div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-8 text-gray-400 text-sm">
        <div class="text-3xl mb-2">☕</div>
        No classes today.
      </div>
    @endif
  </div>

  {{-- MY CLASSES --}}
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-extrabold text-navy">🎓 My Classes</h2>
      <a href="/teacher/classes" class="text-xs text-navy hover:text-gold font-semibold">View all →</a>
    </div>
    @if ($myClasses->count())
      <div class="space-y-2">
        @foreach ($myClasses as $c)
          <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
            <div class="font-semibold text-navy text-sm">{{ $c['class'] }} {{ $c['stream'] }}</div>
            <div class="text-xs text-gray-500">{{ $c['count'] }} students</div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-8 text-gray-400 text-sm">
        <div class="text-3xl mb-2">👨‍🏫</div>
        No classes assigned yet.
      </div>
    @endif
  </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
  <a href="/teacher/timetable" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">📅</div>
    <div class="font-bold text-navy text-sm">My Timetable</div>
  </a>
  <a href="/teacher/marks" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">✍️</div>
    <div class="font-bold text-navy text-sm">Enter Marks</div>
  </a>
  <a href="/teacher/classes" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">🎓</div>
    <div class="font-bold text-navy text-sm">Class Lists</div>
  </a>
  <a href="/teacher/homework" class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition text-center">
    <div class="text-3xl mb-2">📚</div>
    <div class="font-bold text-navy text-sm">Homework</div>
  </a>
</div>

@endsection
