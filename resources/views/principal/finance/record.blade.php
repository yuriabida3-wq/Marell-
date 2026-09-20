@extends('layouts.admin')
@section('title', 'Record Payment')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.finance.index') }}" class="text-xs text-gray-500 hover:text-navy">← Finance</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Record Payment</h1>
  <p class="text-sm text-gray-500">For Cash or Bank payments. M-Pesa is auto-recorded via callback.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

{{-- SEARCH --}}
<div class="bg-white rounded-2xl p-6 shadow mb-4 max-w-2xl">
  <form method="GET" class="flex flex-col sm:flex-row gap-3">
    <input name="adm" value="{{ request('adm') }}" placeholder="Enter ADM number..." required
           class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    <button class="btn btn-navy w-full sm:w-auto">🔍 Find Student</button>
  </form>
</div>

@if ($student)
  <div class="bg-white rounded-2xl p-6 shadow mb-4 max-w-2xl">
    <div class="flex items-start gap-4">
      <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-xl">
        {{ strtoupper(substr($student->name, 0, 1)) }}
      </div>
      <div class="flex-1">
        <div class="font-bold text-navy text-lg">{{ $student->name }}</div>
        <div class="text-sm text-gray-600">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>
      </div>
    </div>
    <div class="grid grid-cols-3 gap-3 mt-4 text-center">
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Total</div>
        <div class="font-bold text-navy text-sm">KES {{ number_format($student->total_fee, 0) }}</div>
      </div>
      <div class="bg-green-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Paid</div>
        <div class="font-bold text-green-700 text-sm">KES {{ number_format($student->paid_amount, 0) }}</div>
      </div>
      <div class="bg-red-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Balance</div>
        <div class="font-bold text-red-600 text-sm">KES {{ number_format($student->balance, 0) }}</div>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
    <form method="POST" action="{{ route('principal.finance.record.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf
      <input type="hidden" name="adm" value="{{ $student->adm_no }}">

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT (KES) *</label>
        <input name="amount" type="number" min="1" step="1" value="{{ old('amount', (int) $student->balance) }}" required
               class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">METHOD *</label>
        <select name="method" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="Cash">Cash</option>
          <option value="Bank">Bank</option>
          <option value="M-Pesa">M-Pesa (manual)</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NOTES (optional)</label>
        <textarea name="notes" rows="2" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:border-gold focus:outline-none">{{ old('notes') }}</textarea>
      </div>

      <button class="md:col-span-2 min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">💾 Record Payment</button>
    </form>
  </div>
@endif

@endsection
