@extends('layouts.public')
@section('title', 'Library')
@section('content')

<section class="bg-navy text-white py-12">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-sm">OUR LIBRARY</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">📚 Marell Library</h1>
    <p class="mt-3 text-white/80">{{ number_format($totalBooks) }} books available to our learners</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-10">
  <div class="card p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input name="q" value="{{ $q }}" placeholder="🔍 Search by title or author" class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <select name="category" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
        <option value="">All Categories</option>
        @foreach ($categories as $c)
          <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
        @endforeach
      </select>
      <button class="md:col-span-3 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Search</button>
    </form>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse ($books as $b)
      <div class="card overflow-hidden hover:shadow-2xl transition">
        <div class="h-48 bg-gradient-to-br from-navy to-[#082f6f] flex items-center justify-center">
          <div class="text-center text-white p-4">
            <div class="text-4xl mb-2">📕</div>
            <div class="font-bold text-sm leading-tight line-clamp-3">{{ $b->title }}</div>
          </div>
        </div>
        <div class="p-3">
          <div class="text-xs text-gray-500 truncate">{{ $b->author ?? 'Unknown' }}</div>
          @if ($b->category)
            <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gold/20 text-navy">{{ $b->category }}</span>
          @endif
          <div class="mt-2 text-xs">
            @if ($b->available_copies > 0)
              <span class="text-green-600 font-bold">● {{ $b->available_copies }} available</span>
            @else
              <span class="text-red-600 font-bold">● All borrowed</span>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="md:col-span-4 text-center py-16 text-gray-400">No books found</div>
    @endforelse
  </div>

  <div class="mt-8">{{ $books->links() }}</div>

  <div class="mt-12 card p-6 text-center">
    <h3 class="font-bold text-navy text-lg mb-2">Students & Parents</h3>
    <p class="text-sm text-gray-600 mb-4">Log in to see your child's borrowed books and due dates.</p>
    <a href="/parent/login" class="btn btn-gold">🔐 Parent Login</a>
  </div>
</section>
@endsection
