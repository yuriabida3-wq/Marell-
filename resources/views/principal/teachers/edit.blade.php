@extends('layouts.admin')
@section('title', 'Edit ' . $teacher->name)
@section('content')
<div class="mb-6">
  <a href="{{ route('principal.teachers.index') }}" class="text-xs text-gray-500">← Teachers</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Edit Teacher</h1>
</div>
@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">👤 Profile</h2>
    <form method="POST" action="{{ route('principal.teachers.update', $teacher) }}" class="space-y-4">
      @csrf @method('PUT')
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">FULL NAME *</label>
        <input name="name" value="{{ old('name', $teacher->name) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EMAIL *</label>
        <input name="email" type="email" value="{{ old('email', $teacher->email) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE</label>
        <input name="phone" value="{{ old('phone', $teacher->phone) }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      </div>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="active" value="1" {{ $teacher->active ? 'checked' : '' }} class="w-5 h-5">
        Active
      </label>
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">💾 Save Changes</button>
    </form>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow h-fit">
    <h2 class="font-extrabold text-navy mb-4">🔐 Reset Password</h2>
    <form method="POST" action="{{ route('principal.teachers.reset-password', $teacher) }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NEW PASSWORD</label>
        <input name="password" type="text" required minlength="6" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      </div>
      <button class="w-full min-h-[48px] rounded-xl bg-navy text-white font-bold">Reset Password</button>
    </form>
  </div>
</div>
@endsection
