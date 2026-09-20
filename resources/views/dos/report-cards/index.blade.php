@extends('layouts.dos')
@section('title', 'Report Cards')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Report Cards</h1>
  <p class="text-sm text-gray-500 mt-1">Generate PDF report cards for an entire class in one click.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
  <h2 class="font-extrabold text-navy mb-4">📄 Bulk Generate</h2>

  @if ($exams->isEmpty() || $classes->isEmpty())
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 text-sm rounded">
      ⚠️ You need at least one exam and one class first.
    </div>
  @else
    <form method="POST" action="{{ route('dos.report-cards.bulk') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EXAM *</label>
        <select name="exam_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="">-- Select Exam --</option>
          @foreach ($exams as $e)
            <option value="{{ $e->id }}">{{ $e->name }} · {{ $e->term }} {{ $e->year }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS *</label>
        <select name="class_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="">-- Select Class --</option>
          @foreach ($classes as $c)
            <option value="{{ $c->id }}">{{ $c->label() }}</option>
          @endforeach
        </select>
      </div>

      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">
        📥 Generate PDF (one page per student)
      </button>

      <p class="text-xs text-gray-500">
        💡 Students without marks for the selected exam will be skipped.
      </p>
    </form>
  @endif
</div>

@endsection
