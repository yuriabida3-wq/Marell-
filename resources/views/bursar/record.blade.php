@extends('layouts.bursar')
@section('title', 'Record Payment')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Record Payment</h1>
  <p class="text-sm text-gray-500 mt-1">Search a student to record cash/bank payment.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" action="{{ route('bursar.record') }}" class="flex gap-2">
    <input name="adm" value="{{ request('adm') }}" placeholder="ADM number..." required autofocus
           class="flex-1 min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
    <button class="min-h-[52px] px-6 rounded-xl bg-navy text-white font-bold">🔍</button>
  </form>
</div>

@if ($student)
  <div class="bg-white rounded-2xl p-5 shadow mb-4">
    <div class="font-bold text-navy text-lg">{{ $student->name }}</div>
    <div class="text-xs text-gray-500">{{ $student->adm_no }} · {{ $student->class }}</div>
    <div class="grid grid-cols-3 gap-2 mt-3 text-center">
      <div class="bg-gray-50 rounded-xl p-2"><div class="text-xs text-gray-500">Total</div><div class="font-bold text-navy text-sm">KES {{ number_format($student->total_fee, 0) }}</div></div>
      <div class="bg-green-50 rounded-xl p-2"><div class="text-xs text-gray-500">Paid</div><div class="font-bold text-green-700 text-sm">KES {{ number_format($student->paid_amount, 0) }}</div></div>
      <div class="bg-red-50 rounded-xl p-2"><div class="text-xs text-gray-500">Balance</div><div class="font-bold text-red-600 text-sm">KES {{ number_format($student->balance, 0) }}</div></div>
    </div>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <form method="POST" action="{{ route('bursar.record.store') }}" class="space-y-3">
      @csrf
      <input type="hidden" name="adm" value="{{ $student->adm_no }}">
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT *</label>
        <input name="amount" type="number" min="1" value="{{ (int) $student->balance }}" required class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">METHOD *</label>
        <select name="method" class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"><option>Cash</option><option>Bank</option><option>M-Pesa</option></select>
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="send_sms" value="1" class="w-5 h-5" checked>
        Send SMS receipt to parent
      </label>
      <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold">💾 Record Payment</button>
    </form>
  </div>
@endif

@endsection
