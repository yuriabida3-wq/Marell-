@extends('layouts.dos')
@section('title', 'Library')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">📚 Library</h1>
    <p class="text-sm text-gray-500 mt-1">Issue, return, track books and fines</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('library.issue') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">📖 Issue Book</a>
    <a href="{{ route('library.return') }}" class="px-4 py-2 rounded-xl bg-navy text-white font-bold text-sm hover:scale-105 transition">↩️ Return Book</a>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-navy rounded-2xl p-5 text-white shadow">
    <div class="text-[10px] tracking-widest font-bold text-gold">TOTAL BOOKS</div>
    <div class="text-3xl font-extrabold mt-1">{{ number_format($totalBooks) }}</div>
  </div>
  <div class="bg-gold rounded-2xl p-5 text-navy shadow">
    <div class="text-[10px] tracking-widest font-bold">AVAILABLE</div>
    <div class="text-3xl font-extrabold mt-1">{{ number_format($availableBooks) }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-navy">ON LOAN</div>
    <div class="text-3xl font-extrabold text-navy mt-1">{{ number_format($onLoan) }}</div>
  </div>
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-red-700">OVERDUE</div>
    <div class="text-3xl font-extrabold text-red-600 mt-1">{{ number_format($overdue) }}</div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <a href="{{ route('library.books') }}" class="card p-6 hover:shadow-xl transition text-center">
    <div class="text-4xl mb-2">📖</div>
    <div class="font-bold text-navy">Book Catalog</div>
    <div class="text-xs text-gray-500 mt-1">Browse, add, edit books</div>
  </a>
  <a href="{{ route('library.loans', ['filter' => 'active']) }}" class="card p-6 hover:shadow-xl transition text-center">
    <div class="text-4xl mb-2">📋</div>
    <div class="font-bold text-navy">Active Loans</div>
    <div class="text-xs text-gray-500 mt-1">{{ $onLoan }} books out</div>
  </a>
  <a href="{{ route('library.loans', ['filter' => 'overdue']) }}" class="card p-6 hover:shadow-xl transition text-center border-2 border-red-200">
    <div class="text-4xl mb-2">⚠️</div>
    <div class="font-bold text-red-700">Overdue</div>
    <div class="text-xs text-gray-500 mt-1">{{ $overdue }} late returns</div>
    <div class="text-xs text-red-600 mt-2 font-bold">Fines: KES {{ number_format($unpaidFines, 0) }}</div>
  </a>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy">📋 Recent Loans</div>
  </div>
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">ISSUED</th>
        <th class="p-3">BOOK</th>
        <th class="p-3">STUDENT</th>
        <th class="p-3">DUE</th>
        <th class="p-3 text-center">STATUS</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($recentLoans as $l)
        <tr class="hover:bg-gray-50">
          <td class="p-3 text-xs text-gray-500">{{ $l->issued_at->format('d M') }}</td>
          <td class="p-3 font-semibold text-navy">{{ $l->book->title ?? '—' }}</td>
          <td class="p-3 text-gray-700">{{ $l->student->name ?? '—' }}</td>
          <td class="p-3 text-xs">{{ $l->due_at->format('d M') }}</td>
          <td class="p-3 text-center">
            @if ($l->returned_at)
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Returned</span>
            @elseif ($l->due_at->isPast())
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Overdue {{ $l->daysOverdue() }}d</span>
            @else
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Out</span>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="p-12 text-center text-gray-400">No loans yet</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
