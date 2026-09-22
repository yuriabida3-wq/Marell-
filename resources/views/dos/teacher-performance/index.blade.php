@extends('layouts.dos')
@section('title', 'Teacher Performance')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">🏆 Teacher Performance</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $month->format('F Y') }} · Ranked by overall score</p>
  </div>
  <form method="POST" action="{{ route('dos.teacher-performance.compute') }}">
    @csrf
    <button class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm">🔄 Recompute Scores</button>
  </form>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="space-y-3">
  @forelse ($rows as $i => $row)
    <div class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-full {{ $i === 0 ? 'bg-gold text-navy' : 'bg-navy text-white' }} flex items-center justify-center font-bold text-lg">
          @if ($i === 0) 🥇
          @elseif ($i === 1) 🥈
          @elseif ($i === 2) 🥉
          @else {{ $i + 1 }}
          @endif
        </div>
        <div class="flex-1">
          <div class="flex items-center justify-between mb-2">
            <div>
              <div class="font-extrabold text-navy text-lg">{{ $row['teacher']->name }}</div>
              <div class="text-xs text-gray-500">{{ $row['teacher']->email }}</div>
            </div>
            <div class="text-right">
              <div class="text-3xl font-extrabold {{ $row['perf']->gradeColor() }}">{{ $row['perf']->score }}</div>
              <div class="text-xs font-bold {{ $row['perf']->gradeColor() }}">Grade {{ $row['perf']->grade() }}</div>
            </div>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-center text-xs">
            <div class="bg-gray-50 rounded-lg p-2">
              <div class="text-gray-500">Attendance</div>
              <div class="font-bold text-navy">{{ $row['perf']->attendance_score }}/30</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-2">
              <div class="text-gray-500">Marks Entry</div>
              <div class="font-bold text-navy">{{ $row['perf']->marks_entry_score }}/20</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-2">
              <div class="text-gray-500">Lesson Plans</div>
              <div class="font-bold text-navy">{{ $row['perf']->lesson_plan_score }}/25</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-2">
              <div class="text-gray-500">Class Perf</div>
              <div class="font-bold text-navy">{{ $row['perf']->class_performance_score }}/25</div>
            </div>
          </div>

          <div class="mt-3 bg-gray-200 rounded-full h-2 overflow-hidden">
            <div class="h-full {{ $row['perf']->score >= 80 ? 'bg-green-500' : ($row['perf']->score >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $row['perf']->score }}%"></div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow">No teachers registered</div>
  @endforelse
</div>
@endsection
