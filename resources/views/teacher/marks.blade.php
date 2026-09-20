@extends('layouts.teacher')
@section('title', 'Enter Marks')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Enter Marks</h1>
  <p class="text-sm text-gray-500 mt-1">Pick exam, class, subject — enter marks for your students.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

@if ($exams->isEmpty())
  <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 text-sm rounded">
    ⚠️ No exams are open for marks entry. Ask the DOS to open one.
  </div>
@endif

<div class="bg-white rounded-2xl p-5 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <select name="exam_id" required class="min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">-- Exam --</option>
      @foreach ($exams as $e)
        <option value="{{ $e->id }}" @selected($exam && $exam->id===$e->id)>{{ $e->name }}</option>
      @endforeach
    </select>

    <select name="class" required class="min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">-- Class --</option>
      @foreach ($classKeys as $ck)
        <option value="{{ $ck->class }}" @selected(request('class')===$ck->class)>{{ $ck->class }} {{ $ck->stream }}</option>
      @endforeach
    </select>
    <input type="hidden" name="stream" value="{{ request('stream') }}">

    <select name="subject" required class="min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">-- Subject --</option>
      @foreach ($subjects as $sub)
        <option value="{{ $sub }}" @selected(request('subject')===$sub)>{{ $sub }}</option>
      @endforeach
    </select>

    <button class="min-h-[48px] rounded-xl bg-navy text-white font-semibold text-sm">🔍 Load</button>
  </form>
</div>

@if ($exam && $classKey && $students->count())
  <form method="POST" action="{{ route('teacher.marks.store') }}">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
    <input type="hidden" name="class" value="{{ $classKey['class'] }}">
    <input type="hidden" name="stream" value="{{ $classKey['stream'] }}">
    <input type="hidden" name="subject" value="{{ request('subject') }}">

    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div class="p-4 bg-gray-50 border-b">
        <div class="font-bold text-navy text-sm">{{ $exam->name }} · {{ request('subject') }} · {{ $classKey['class'] }} {{ $classKey['stream'] }}</div>
        <div class="text-xs text-gray-500">{{ $students->count() }} students · Grades auto-computed</div>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-navy text-white text-xs">
          <tr><th class="p-2 text-left">STUDENT</th><th class="p-2 text-center w-32">MARKS</th><th class="p-2 text-center w-24">GRADE</th></tr>
        </thead>
        <tbody class="divide-y">
          @foreach ($students as $s)
            @php $exist = $existing[$s->id] ?? null; @endphp
            <tr>
              <td class="p-2">
                <div class="font-semibold text-navy text-sm">{{ $s->name }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ $s->adm_no }}</div>
              </td>
              <td class="p-2 text-center">
                <input type="number" name="marks[{{ $s->id }}]" min="0" max="100"
                       value="{{ $exist->marks ?? '' }}"
                       class="w-24 h-10 rounded-lg border-2 border-gray-200 text-center text-sm focus:border-gold focus:outline-none markInput"
                       oninput="updateGrade(this)">
              </td>
              <td class="p-2 text-center font-bold gradeCell text-gray-400">{{ $exist->grade ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <button class="mt-4 w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold shadow-lg">💾 Save Marks</button>
  </form>
@endif

@push('scripts')
<script>
  function gradeFromMarks(m) {
    if (m >= 80) return 'A'; if (m >= 75) return 'A-';
    if (m >= 70) return 'B+'; if (m >= 65) return 'B'; if (m >= 60) return 'B-';
    if (m >= 55) return 'C+'; if (m >= 50) return 'C'; if (m >= 45) return 'C-';
    if (m >= 40) return 'D+'; if (m >= 35) return 'D';
    return 'E';
  }
  function updateGrade(input) {
    const cell = input.closest('tr').querySelector('.gradeCell');
    const val  = parseInt(input.value);
    if (isNaN(val)) { cell.textContent = '—'; cell.className = 'p-2 text-center font-bold gradeCell text-gray-400'; return; }
    const g = gradeFromMarks(val);
    cell.textContent = g;
    cell.className = 'p-2 text-center font-bold gradeCell ' +
      (['A','A-'].includes(g) ? 'text-green-600' :
       ['B+','B','B-'].includes(g) ? 'text-blue-600' :
       ['C+','C','C-'].includes(g) ? 'text-yellow-600' : 'text-red-600');
  }
</script>
@endpush

@endsection
