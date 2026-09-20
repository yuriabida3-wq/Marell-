@extends('layouts.dos')
@section('title', $exam->name)
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.exams.index') }}" class="text-xs text-gray-500 hover:text-navy">← Exams</a>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-2">
    <div>
      <h1 class="text-2xl md:text-3xl font-extrabold text-navy">{{ $exam->name }}</h1>
      <p class="text-sm text-gray-500">{{ $exam->term }} · {{ $exam->year }}</p>
    </div>
    @php
      $cls = match($exam->status) {
        'draft'     => 'bg-gray-100 text-gray-700',
        'open'      => 'bg-blue-100 text-blue-700',
        'closed'    => 'bg-yellow-100 text-yellow-700',
        'published' => 'bg-green-100 text-green-700',
      };
    @endphp
    <span class="px-4 py-2 rounded-full text-sm font-bold {{ $cls }}">{{ strtoupper($exam->status) }}</span>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

{{-- STATUS CONTROLS --}}
<div class="bg-white rounded-2xl p-5 shadow mb-6">
  <h2 class="font-extrabold text-navy mb-3">⚙️ Exam Status</h2>
  <div class="flex flex-wrap gap-2">
    <form method="POST" action="{{ route('dos.exams.status', $exam) }}" class="inline">
      @csrf
      <input type="hidden" name="status" value="open">
      <button class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:scale-105 transition {{ $exam->status === 'open' ? 'opacity-50' : '' }}">Open for Marks Entry</button>
    </form>
    <form method="POST" action="{{ route('dos.exams.status', $exam) }}" class="inline">
      @csrf
      <input type="hidden" name="status" value="closed">
      <button class="px-4 py-2 rounded-xl bg-yellow-600 text-white text-sm font-semibold hover:scale-105 transition {{ $exam->status === 'closed' ? 'opacity-50' : '' }}">Close Entry</button>
    </form>
    <form method="POST" action="{{ route('dos.exams.publish', $exam) }}" class="inline" onsubmit="return confirm('Publish results and send SMS to parents?');">
      @csrf
      <button class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:scale-105 transition {{ $exam->status === 'published' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $exam->status !== 'closed' ? 'disabled' : '' }}>🚀 Publish + SMS Parents</button>
    </form>
  </div>
  <p class="text-xs text-gray-500 mt-3">Workflow: <strong>draft</strong> → <strong>open</strong> (teachers enter marks) → <strong>closed</strong> (lock) → <strong>published</strong> (parents notified).</p>
</div>

{{-- CLASS SUMMARY --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b">
    <h2 class="font-extrabold text-navy">📊 Entry Progress by Class</h2>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">CLASS</th>
          <th class="p-3 text-center">STUDENTS</th>
          <th class="p-3 text-center">MARKS ENTRIES</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($summary as $row)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-semibold text-navy">{{ $row['class']->label() }}</td>
            <td class="p-3 text-center">{{ $row['students'] }}</td>
            <td class="p-3 text-center font-bold">{{ $row['entries'] }}</td>
            <td class="p-3 text-center">
              @if ($row['students'] === 0)
                <span class="text-xs text-gray-400">No students</span>
              @elseif ($row['entries'] === 0)
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Not started</span>
              @else
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">In progress</span>
              @endif
            </td>
            <td class="p-3 text-right">
              <a href="{{ route('dos.marks', ['exam_id' => $exam->id, 'class_id' => $row['class']->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">Enter Marks →</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-gray-400 text-sm">Create classes first</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
