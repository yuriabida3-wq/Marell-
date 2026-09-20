@extends('layouts.dos')
@section('title', 'Missing Marks')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Missing Marks Report</h1>
  <p class="text-sm text-gray-500 mt-1">See which classes still have pending marks entry per exam.</p>
</div>

<div class="bg-white rounded-2xl p-5 shadow mb-4">
  <form method="GET" class="flex flex-col sm:flex-row gap-3">
    <select name="exam_id" required class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      <option value="">-- Select Exam --</option>
      @foreach ($exams as $e)
        <option value="{{ $e->id }}" @selected($exam && $exam->id === $e->id)>{{ $e->name }} · {{ $e->term }} {{ $e->year }}</option>
      @endforeach
    </select>
    <button class="btn btn-navy">🔍 Generate Report</button>
  </form>
</div>

@if ($exam)
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b">
      <h2 class="font-extrabold text-navy">{{ $exam->name }} — Entry Progress</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-3">CLASS</th>
            <th class="p-3 text-center">STUDENTS</th>
            <th class="p-3 text-center">SUBJECTS ENTERED</th>
            <th class="p-3 text-center">STUDENTS W/O MARKS</th>
            <th class="p-3 text-right">ACTION</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($report as $row)
            <tr class="{{ $row['students_missing'] > 0 ? 'bg-red-50' : 'bg-green-50' }}">
              <td class="p-3 font-semibold text-navy">{{ $row['class']->label() }}</td>
              <td class="p-3 text-center">{{ $row['students'] }}</td>
              <td class="p-3 text-center font-bold">{{ $row['subjects_entered'] }} subjects</td>
              <td class="p-3 text-center">
                @if ($row['students_missing'] > 0)
                  <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $row['students_missing'] }} pending</span>
                @else
                  <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">✓ Complete</span>
                @endif
              </td>
              <td class="p-3 text-right">
                <a href="{{ route('dos.marks', ['exam_id' => $exam->id, 'class_id' => $row['class']->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">Enter →</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-8 text-center text-gray-400 text-sm">No classes</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endif

@endsection
