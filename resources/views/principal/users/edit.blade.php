@extends('layouts.admin')
@section('title', 'Edit ' . $user->name)
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.users.index') }}" class="text-xs text-gray-500 hover:text-navy">← Users</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Edit User</h1>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif

@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4">
    <ul class="text-sm text-red-700 list-disc list-inside">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  {{-- PROFILE --}}
  <div class="bg-white rounded-2xl p-6 md:p-8 shadow">
    <h2 class="font-extrabold text-navy mb-4">👤 Profile</h2>
    <form method="POST" action="{{ route('principal.users.update', $user) }}" class="grid grid-cols-1 gap-4">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NAME *</label>
        <input name="name" value="{{ old('name', $user->name) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EMAIL *</label>
        <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ROLE *</label>
        <select name="role" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          @foreach ($roles as $r)
            <option value="{{ $r }}" @selected($user->hasRole($r))>{{ ucfirst($r) }}</option>
          @endforeach
        </select>
      </div>

      <button class="min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">💾 Save Changes</button>
    </form>
  </div>

  {{-- PASSWORD RESET --}}
  <div class="bg-white rounded-2xl p-6 md:p-8 shadow h-fit">
    <h2 class="font-extrabold text-navy mb-4">🔐 Reset Password</h2>
    <form method="POST" action="{{ route('principal.users.reset-password', $user) }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NEW PASSWORD *</label>
        <input name="password" type="password" required minlength="6" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <button class="w-full min-h-[48px] rounded-xl bg-navy text-white font-bold hover:scale-[1.02] transition">Reset Password</button>
    </form>
  </div>

</div>

@endsection
