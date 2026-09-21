@extends('layouts.dos')
@section('title', 'Register Student')
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.students.index') }}" class="text-xs text-gray-500">← All Students</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Register New Student</h1>
  <p class="text-sm text-gray-500">Next ADM: <span class="font-mono font-bold text-navy">{{ $nextAdm }}</span> (auto)</p>
</div>

@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4">
    <ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
  </div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ route('dos.students.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STUDENT NAME *</label>
      <input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS *</label>
      <select name="class" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <option value="">-- Select --</option>
        @foreach ($classes as $c)
          <option value="{{ $c }}" @selected(old('class') === $c)>{{ $c }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STREAM</label>
      <select name="stream" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <option value="">-- None --</option>
        @foreach ($streams as $s)
          <option value="{{ $s }}" @selected(old('stream') === $s)>{{ $s }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PARENT / GUARDIAN NAME *</label>
      <input name="parent_name" value="{{ old('parent_name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PARENT PHONE *</label>
      <input name="parent_phone" value="{{ old('parent_phone') }}" required placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PARENT EMAIL</label>
      <input name="parent_email" type="email" value="{{ old('parent_email') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TOTAL FEE (KES) *</label>
      <input name="total_fee" type="number" min="0" step="100" value="{{ old('total_fee', 45000) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div class="md:col-span-2 flex gap-3">
      <button class="flex-1 min-h-[48px] rounded-xl bg-gold text-navy font-bold">✅ Register Student</button>
      <a href="{{ route('dos.students.index') }}" class="flex-1 min-h-[48px] rounded-xl bg-gray-100 text-gray-700 font-semibold text-center leading-[48px]">Cancel</a>
    </div>
  </form>
</div>
@endsection
