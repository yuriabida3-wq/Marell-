@extends('layouts.admin')
@section('title', 'Add Teacher')
@section('content')
<div class="mb-6">
  <a href="{{ route('principal.teachers.index') }}" class="text-xs text-gray-500">← Teachers</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Add New Teacher</h1>
</div>
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif
<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
  <form method="POST" action="{{ route('principal.teachers.store') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">FULL NAME *</label>
      <input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EMAIL *</label>
      <input name="email" type="email" value="{{ old('email') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE</label>
      <input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PASSWORD * (min 6 chars)</label>
      <input name="password" type="text" required minlength="6" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">✅ Register Teacher</button>
  </form>
</div>
@endsection
