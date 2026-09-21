@extends('layouts.admin')
@section('title', 'Check In Visitor')
@section('content')

<div class="mb-6">
  <a href="{{ route('visitors.gate') }}" class="text-xs text-gray-500">← Gate</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">➕ Check In Visitor</h1>
</div>

@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ route('visitors.check-in.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">VISITOR NAME *</label>
      <input name="name" value="{{ old('name') }}" required autofocus class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-lg">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE *</label>
      <input name="phone" value="{{ old('phone') }}" required placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ID NUMBER</label>
      <input name="id_number" value="{{ old('id_number') }}" placeholder="National ID / Passport" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PURPOSE OF VISIT *</label>
      <input name="purpose" value="{{ old('purpose') }}" required placeholder="e.g. Meet the Principal, Deliver package" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">HOST NAME</label>
      <input name="host_name" value="{{ old('host_name') }}" placeholder="Who are they seeing?" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">HOST TYPE</label>
      <select name="host_type" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <option value="">-- Select --</option>
        <option value="admin"   @selected(old('host_type') === 'admin')>Admin</option>
        <option value="staff"   @selected(old('host_type') === 'staff')>Staff</option>
        <option value="student" @selected(old('host_type') === 'student')>Student</option>
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STUDENT ADM (if visiting a student)</label>
      <input name="student_adm" value="{{ old('student_adm') }}" placeholder="e.g. MAR-2024-0001" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">VEHICLE PLATE</label>
      <input name="vehicle_plate" value="{{ old('vehicle_plate') }}" placeholder="KDA 123X" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NOTES</label>
      <textarea name="notes" rows="2" class="w-full rounded-xl border-2 border-gray-200 px-4 py-2">{{ old('notes') }}</textarea>
    </div>

    <div class="md:col-span-2">
      <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">✅ Check In & Issue Badge</button>
    </div>
  </form>
</div>
@endsection
