@extends('layouts.bursar')
@section('title', 'Find Student')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Find Student</h1>
  <p class="text-sm text-gray-500 mt-1">Type ADM number to view balance and take action.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

{{-- SEARCH --}}
<div class="bg-white rounded-2xl p-4 shadow mb-4 sticky top-16 z-20">
  <form method="GET" action="{{ route('bursar.students') }}" class="flex gap-2">
    <input name="adm" value="{{ request('adm') }}" required placeholder="e.g. MAR-2024-0001" autofocus
           class="flex-1 min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
    <button class="min-h-[52px] px-6 rounded-xl bg-navy text-white font-bold">🔍</button>
  </form>
</div>

@if (request('adm') && !$student)
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4">
    <div class="font-bold text-red-700">No student found with ADM "{{ request('adm') }}"</div>
  </div>
@endif

@if ($student)
  <div class="bg-white rounded-2xl shadow overflow-hidden mb-4">
    <div class="p-5 bg-gradient-to-br from-navy to-[#082f6f] text-white">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-2xl flex-shrink-0">
          {{ strtoupper(substr($student->name, 0, 1)) }}
        </div>
        <div class="flex-1">
          <div class="font-extrabold text-xl">{{ $student->name }}</div>
          <div class="text-xs opacity-80">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>
          <div class="text-xs opacity-80 mt-1">👤 {{ $student->parent_name }} · 📞 {{ $student->parent_phone }}</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-3 divide-x">
      <div class="p-4 text-center">
        <div class="text-[10px] text-gray-500 tracking-widest">TOTAL</div>
        <div class="text-lg md:text-xl font-bold text-navy mt-1">KES {{ number_format($student->total_fee, 0) }}</div>
      </div>
      <div class="p-4 text-center bg-green-50">
        <div class="text-[10px] text-gray-500 tracking-widest">PAID</div>
        <div class="text-lg md:text-xl font-bold text-green-700 mt-1">KES {{ number_format($student->paid_amount, 0) }}</div>
      </div>
      <div class="p-4 text-center bg-red-50">
        <div class="text-[10px] text-gray-500 tracking-widest">BALANCE</div>
        <div class="text-lg md:text-xl font-bold text-red-600 mt-1">KES {{ number_format($student->balance, 0) }}</div>
      </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-2 bg-gray-50">
      <a href="{{ route('bursar.students') }}?adm={{ $student->adm_no }}#record" class="quick-btn bg-gold text-navy text-xs">➕ Record Payment</a>
      <a href="{{ route('bursar.payments') }}?q={{ $student->adm_no }}" class="quick-btn bg-navy text-white text-xs">📄 History</a>
      <a href="{{ route('bursar.statement', $student) }}" class="quick-btn bg-white border-2 border-navy text-navy text-xs">📋 Statement</a>
      <form method="POST" action="{{ route('bursar.balance-sms', $student) }}" class="contents">
        @csrf
        <button class="quick-btn bg-white border-2 border-gold text-navy text-xs">📱 Send SMS</button>
      </form>
    </div>
  </div>

  {{-- QUICK RECORD --}}
  <div id="record" class="bg-white rounded-2xl p-6 shadow mt-4">
    <h2 class="font-extrabold text-navy mb-4">➕ Quick Record Payment</h2>
    <form method="POST" action="{{ route('bursar.record.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
      @csrf
      <input type="hidden" name="adm" value="{{ $student->adm_no }}">

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT *</label>
        <input name="amount" type="number" min="1" step="1" value="{{ (int) $student->balance }}" required
               class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none text-lg">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">METHOD *</label>
        <select name="method" class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option>Cash</option><option>Bank</option><option>M-Pesa</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NOTES</label>
        <input name="notes" placeholder="Reference / remarks" class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <label class="md:col-span-2 flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="send_sms" value="1" class="w-5 h-5" checked>
        Send SMS receipt to parent
      </label>

      <button class="md:col-span-2 min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">💾 Record & Print Receipt</button>
    </form>
  </div>
@endif

@endsection
