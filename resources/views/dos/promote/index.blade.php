@extends('layouts.dos')
@section('title', 'Promote Students')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Promote Students</h1>
  <p class="text-sm text-gray-500 mt-1">Move an entire class to the next level in one click.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
  <form method="POST" action="{{ route('dos.promote.run') }}" class="space-y-4" onsubmit="return confirm('Promote all active students? This cannot be undone easily.');">
    @csrf

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">FROM CLASS *</label>
      <select name="from_class_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        <option value="">-- Select --</option>
        @foreach ($classes as $c)
          <option value="{{ $c->id }}">{{ $c->label() }} ({{ $c->studentCount() }} students)</option>
        @endforeach
      </select>
    </div>

    <div class="text-center text-3xl text-gold">⬇️</div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TO CLASS *</label>
      <select name="to_class_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        <option value="">-- Select --</option>
        @foreach ($classes as $c)
          <option value="{{ $c->id }}">{{ $c->label() }}</option>
        @endforeach
      </select>
    </div>

    <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">
      ⬆️ Promote Students
    </button>

    <div class="bg-red-50 border-l-4 border-red-500 p-3 text-xs text-red-700 rounded">
      ⚠️ All active students in the From Class will be reassigned to the To Class. This is a permanent change.
    </div>
  </form>
</div>

@endsection
