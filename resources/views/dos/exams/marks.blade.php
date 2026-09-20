@extends('layouts.dos')
@section('title', 'Marks Entry')
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.exams.index') }}" class="text-xs text-gray-500 hover:text-navy">← Exams</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Marks Entry</h1>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

{{-- SELECT EXAM + CLASS --}}
<div class="bg-white rounded-2xl p-5 shadow mb-4">
  <form method="GET" action="{{ route('dos.marks') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <select name="exam_id" required class="min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      <option value="">-- Select Exam --</option>
      @foreach ($exams as $e)
        <option value="{{ $e->id }}" @selected($exam && $exam->id === $e->id)>{{ $e->name }} · {{ $e->term }} {{ $e->year }}</option>
      @endforeach
    </select>

    <select name="class_id" required class="min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      <option value="">-- Select Class --</option>
      @foreach ($classes as $c)
        <option value="{{ $c->id }}" @selected($class && $class->id === $c->id)>{{ $c->label() }}</option>
      @endforeach
    </select>

    <button class="min-h-[48px] rounded-xl bg-navy text-white font-semibold hover:scale-[1.02] transition">🔍 Load</button>
  </form>
</div>

@if ($exam && $class)
  <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <div class="text-lg font-bold text-navy">{{ $exam->name }} · {{ $class->label() }}</div>
      <div class="text-xs text-gray-500">{{ $students->count() }} students</div>
    </div>
    <div class="text-xs text-gray-500 bg-yellow-50 border-l-4 border-yellow-500 px-3 py-2 rounded">
      💡 Enter marks 0–100. Leave blank to skip a subject.
    </div>
  </div>

  @if ($students->isEmpty())
    <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow">No students in this class.</div>
  @else
    <form method="POST" action="{{ route('dos.marks.store') }}">
      @csrf
      <input type="hidden" name="exam_id" value="{{ $exam->id }}">
      <input type="hidden" name="class_id" value="{{ $class->id }}">

      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-navy text-white sticky top-0">
              <tr>
                <th class="p-2 text-left sticky left-0 bg-navy z-10 min-w-[180px]">STUDENT</th>
                @foreach ($subjects as $sub)
                  <th class="p-2 text-center min-w-[80px]">{{ \Illuminate\Support\Str::limit($sub, 10) }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach ($students as $s)
                <tr class="hover:bg-gray-50">
                  <td class="p-2 sticky left-0 bg-white z-10 font-semibold text-navy">
                    {{ $s->name }}
                    <div class="text-[10px] text-gray-500 font-mono">{{ $s->adm_no }}</div>
                  </td>
                  @foreach ($subjects as $sub)
                    @php
                      $existing = $existing[$s->id][$sub] ?? null;
                    @endphp
                    <td class="p-1 text-center">
                      <input type="number" min="0" max="100"
                             name="marks[{{ $s->id }}][{{ $sub }}]"
                             value="{{ $existing->marks ?? '' }}"
                             class="w-full max-w-[70px] h-9 rounded-lg border-2 border-gray-200 text-center text-xs focus:border-gold focus:outline-none">
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-4 flex gap-3 sticky bottom-4">
        <button class="flex-1 min-h-[52px] rounded-xl bg-gold text-navy font-bold shadow-2xl hover:scale-[1.02] transition">💾 Save All Marks</button>
      </div>
    </form>
  @endif
@endif

@endsection
