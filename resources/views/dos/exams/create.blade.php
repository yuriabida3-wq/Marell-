@extends('layouts.dos')
@section('title', 'Create Exam')
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.exams.index') }}" class="text-xs text-gray-500 hover:text-navy">← Exams</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Create Exam</h1>
</div>

@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4">
    <ul class="text-sm text-red-700 list-disc list-inside">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
  <form method="POST" action="{{ route('dos.exams.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EXAM NAME *</label>
      <input name="name" value="{{ old('name') }}" required placeholder="e.g. End of Term 1 Exam" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TERM *</label>
      <select name="term" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        <option value="Term 1">Term 1</option>
        <option value="Term 2">Term 2</option>
        <option value="Term 3">Term 3</option>
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">YEAR *</label>
      <input name="year" type="number" value="{{ old('year', date('Y')) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <button class="md:col-span-2 min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">✅ Create Exam</button>
  </form>
</div>

@endsection
