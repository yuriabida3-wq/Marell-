@extends('layouts.public')
@section('title', 'Library Books')
@section('content')

<section class="bg-navy text-white py-10">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="text-gold font-bold tracking-widest text-xs">PARENT PORTAL</div>
        <h1 class="text-2xl md:text-3xl font-extrabold mt-1">📚 Library Books</h1>
        <p class="text-white/70 text-sm mt-1">Books your child has borrowed</p>
      </div>
      <a href="{{ route('parent.dashboard') }}" class="btn btn-outline text-sm !min-h-[42px]">← Dashboard</a>
    </div>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-8">

  @if ($children->count() > 1)
    <div class="mb-6">
      <div class="text-xs text-gray-500 font-semibold tracking-widest mb-2">SWITCH CHILD</div>
      <div class="flex flex-wrap gap-2">
        @foreach ($children as $c)
          <a href="{{ route('parent.library', ['child' => $c->id]) }}"
             class="px-4 py-2 rounded-xl text-sm font-semibold {{ $c->id === $selected->id ? 'bg-navy text-white' : 'bg-gray-200 text-navy' }}">
            {{ $c->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  <div class="card p-5 mb-6">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-xl">
        {{ strtoupper(substr($selected->name, 0, 1)) }}
      </div>
      <div class="flex-1">
        <div class="font-extrabold text-navy text-lg">{{ $selected->name }}</div>
        <div class="text-sm text-gray-600">{{ $selected->adm_no }} · {{ $selected->class }} {{ $selected->stream }}</div>
      </div>
      @if ($totalFines > 0)
        <div class="text-right">
          <div class="text-xs text-gray-500">Unpaid Fines</div>
          <div class="font-bold text-red-600">KES {{ number_format($totalFines, 0) }}</div>
        </div>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-navy rounded-2xl p-5 text-white shadow">
      <div class="text-xs tracking-widest font-bold text-gold">BOOKS OUT</div>
      <div class="text-3xl font-extrabold mt-1">{{ $activeLoans->count() }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow">
      <div class="text-xs tracking-widest font-bold text-navy">OVERDUE</div>
      <div class="text-3xl font-extrabold text-red-600 mt-1">{{ $activeLoans->filter(fn($l) => $l->due_at->isPast())->count() }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow">
      <div class="text-xs tracking-widest font-bold text-gray-500">BORROWED EVER</div>
      <div class="text-3xl font-extrabold text-navy mt-1">{{ $history->count() + $activeLoans->count() }}</div>
    </div>
  </div>

  {{-- CURRENTLY OUT --}}
  <div class="card overflow-hidden mb-6">
    <div class="p-5 border-b bg-blue-50">
      <div class="font-extrabold text-navy">📖 Currently Borrowed</div>
    </div>
    @if ($activeLoans->count())
      <div class="divide-y">
        @foreach ($activeLoans as $l)
          @php $overdue = $l->due_at->isPast(); @endphp
          <div class="p-4 flex items-center justify-between {{ $overdue ? 'bg-red-50' : '' }}">
            <div>
              <div class="font-semibold text-navy">{{ $l->book->title }}</div>
              <div class="text-xs text-gray-500">{{ $l->book->author }}</div>
              <div class="text-xs mt-1">
                Issued: {{ $l->issued_at->format('d M') }}
                · Due: <span class="{{ $overdue ? 'text-red-600 font-bold' : 'text-gray-700' }}">{{ $l->due_at->format('d M Y') }}</span>
              </div>
            </div>
            <div class="text-right">
              @if ($overdue)
                <div class="text-xs text-red-600 font-bold">OVERDUE {{ $l->daysOverdue() }} days</div>
                <div class="text-xs text-red-600 mt-1">Fine: KES {{ number_format($l->calculateFine(), 0) }}</div>
              @else
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">On time</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="p-12 text-center text-gray-400">
        <div class="text-4xl mb-2">📚</div>
        No books currently borrowed
      </div>
    @endif
  </div>

  {{-- HISTORY --}}
  @if ($history->count())
    <div class="card overflow-hidden">
      <div class="p-5 border-b">
        <div class="font-extrabold text-navy">📋 Recent Returns</div>
      </div>
      <div class="divide-y">
        @foreach ($history as $l)
          <div class="p-4 flex items-center justify-between">
            <div>
              <div class="font-semibold text-navy text-sm">{{ $l->book->title }}</div>
              <div class="text-xs text-gray-500">Returned {{ $l->returned_at->format('d M Y') }}</div>
            </div>
            @if ($l->fine > 0)
              <div class="text-xs {{ $l->fine_paid ? 'text-gray-400 line-through' : 'text-red-600 font-bold' }}">
                Fine: KES {{ number_format($l->fine, 0) }}
              </div>
            @else
              <div class="text-xs text-green-600">✓ No fine</div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif
</section>
@endsection
