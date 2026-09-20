@extends('layouts.admin')
@section('title', 'Add User')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.users.index') }}" class="text-xs text-gray-500 hover:text-navy">← Users</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Add User</h1>
</div>

@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4">
    <ul class="text-sm text-red-700 list-disc list-inside">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
  <form method="POST" action="{{ route('principal.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NAME *</label>
      <input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EMAIL *</label>
      <input name="email" type="email" value="{{ old('email') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE</label>
      <input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PASSWORD *</label>
      <input name="password" type="password" required minlength="6" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ROLE *</label>
      <select name="role" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        @foreach ($roles as $r)
          <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst($r) }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-2 flex gap-3">
      <button class="flex-1 min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">✅ Create User</button>
      <a href="{{ route('principal.users.index') }}" class="flex-1 min-h-[48px] rounded-xl bg-gray-100 text-gray-700 font-semibold text-center leading-[48px] hover:bg-gray-200 transition">Cancel</a>
    </div>
  </form>
</div>

@endsection
