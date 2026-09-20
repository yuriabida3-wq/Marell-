@extends('layouts.dos')
@section('title', 'Exams')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Exams</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $exams->total() }} total exams</p>
  </div>
  <a href="{{ route('dos.exams.create') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Create Exam</a>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  @forelse ($exams as $exam)
    <div class="bg-white rounded-2xl p-5 shadow hover:shadow-lg transition">
      <div class="flex items-start justify-between mb-3">
        <div>
          <div class="font-extrabold text-navy text-lg">{{ $exam->name }}</div>
          <div class="text-xs text-gray-500">{{ $exam->term }} · {{ $exam->year }}</div>
        </div>
        @php
          $cls = match($exam->status) {
            'draft'     => 'bg-gray-100 text-gray-700',
            'open'      => 'bg-blue-100 text-blue-700',
            'closed'    => 'bg-yellow-100 text-yellow-700',
            'published' => 'bg-green-100 text-green-700',
          };
        @endphp
        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $cls }}">{{ ucfirst($exam->status) }}</span>
      </div>

      <div class="text-sm text-gray-600 mb-4">
        <div class="flex justify-between"><span>Marks entries:</span><span class="font-bold text-navy">{{ $exam->results_count }}</span></div>
      </div>

      <div class="flex gap-2">
        <a href="{{ route('dos.exams.show', $exam) }}" class="flex-1 text-center px-3 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:scale-105 transition">Manage</a>
        <a href="{{ route('dos.marks', ['exam_id' => $exam->id]) }}" class="flex-1 text-center px-3 py-2 rounded-xl bg-gold text-navy text-xs font-semibold hover:scale-105 transition">Enter Marks</a>
      </div>
    </div>
  @empty
    <div class="md:col-span-2 lg:col-span-3 text-center py-16 text-gray-400">
      <div class="text-4xl mb-2">📝</div>
      No exams yet. Click <strong>+ Create Exam</strong> to start.
    </div>
  @endforelse
</div>

<div class="mt-4">{{ $exams->links() }}</div>

@endsection
