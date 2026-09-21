@extends('layouts.admin')
@section('title', 'Pre-Register Visitor')
@section('content')

<div class="mb-6">
  <a href="{{ route('visitors.gate') }}" class="text-xs text-gray-500">← Gate</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">⏳ Pre-Register Expected Visitor</h1>
  <p class="text-sm text-gray-500">Add a visitor expected today so security knows who's coming.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ route('visitors.pre-register.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf
    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">VISITOR NAME *</label>
      <input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE *</label>
      <input name="phone" value="{{ old('phone') }}" required placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">HOST NAME</label>
      <input name="host_name" value="{{ old('host_name') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">HOST TYPE</label>
      <select name="host_type" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <option value="">-- Select --</option>
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
        <option value="student">Student</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STUDENT ADM</label>
      <input name="student_adm" value="{{ old('student_adm') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PURPOSE *</label>
      <input name="purpose" value="{{ old('purpose') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div class="md:col-span-2">
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">Pre-Register</button>
    </div>
  </form>
</div>
@endsection
