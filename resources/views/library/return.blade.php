@extends('layouts.dos')
@section('title', 'Return Book')
@section('content')

<div class="mb-6">
  <a href="{{ route('library.index') }}" class="text-xs text-gray-500">← Library</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">↩️ Return Book</h1>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="flex gap-2">
    <input name="adm" value="{{ $adm }}" placeholder="Enter Student ADM number..." required autofocus
           class="flex-1 min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
    <button class="min-h-[52px] px-6 rounded-xl bg-navy text-white font-bold">🔍 Find</button>
  </form>
</div>

@if ($student)
  <div class="bg-white rounded-2xl p-5 shadow mb-4">
    <div class="font-bold text-navy text-lg">{{ $student->name }}</div>
    <div class="text-sm text-gray-600">{{ $student->adm_no }} · {{ $student->class }}</div>
  </div>

  @if ($activeLoans->count() > 0)
    <div class="space-y-3">
      @foreach ($activeLoans as $l)
        @php
          $overdue = $l->due_at->isPast();
          $fine = $l->calculateFine();
        @endphp
        <div class="bg-white rounded-2xl p-4 shadow flex items-center justify-between {{ $overdue ? 'border-2 border-red-200' : '' }}">
          <div>
            <div class="font-bold text-navy">{{ $l->book->title }}</div>
            <div class="text-xs text-gray-500 mt-1">Issued: {{ $l->issued_at->format('d M') }} · Due: {{ $l->due_at->format('d M Y') }}</div>
            @if ($overdue)
              <div class="text-xs text-red-600 font-bold mt-1">⚠️ Overdue by {{ $l->daysOverdue() }} days · Fine: KES {{ number_format($fine, 0) }}</div>
            @endif
          </div>
          <form method="POST" action="{{ route('library.loans.return', $l) }}">
            @csrf
            <button class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-bold hover:scale-105 transition">Return</button>
          </form>
        </div>
      @endforeach
    </div>
  @else
    <div class="bg-white rounded-2xl p-12 text-center text-gray-400">
      <div class="text-4xl mb-2">📚</div>
      {{ $student->name }} has no books on loan.
    </div>
  @endif
@endif
@endsection
