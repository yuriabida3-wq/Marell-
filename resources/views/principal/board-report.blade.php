@extends('layouts.admin')
@section('title', 'Board Report')
@section('content')
<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Board Report</h1>
  <p class="text-sm text-gray-500 mt-1">Professional PDF for board meetings</p>
</div>

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-2xl">
  <form method="POST" action="{{ route('principal.board-report.generate') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">FROM DATE</label>
      <input type="date" name="from" value="{{ now()->startOfMonth()->toDateString() }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TO DATE</label>
      <input type="date" name="to" value="{{ now()->toDateString() }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>
    <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">Generate Board PDF</button>
    <p class="text-xs text-gray-500">Includes: income, expenses, net, cashflow by method, class-wise breakdown, expense categories, daily trend, signatures.</p>
  </form>
</div>
@endsection
