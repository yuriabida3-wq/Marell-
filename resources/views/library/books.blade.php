@extends(role_layout())
@section('title', 'Book Catalog')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <a href="{{ route('library.index') }}" class="text-xs text-gray-500">← Library</a>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">📖 Book Catalog</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $books->total() }} books</p>
  </div>
  <a href="{{ route('library.books.create') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Add Book</a>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <input name="q" value="{{ $q }}" placeholder="🔍 Search title, author, ISBN..." class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
    <select name="category" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">All Categories</option>
      @foreach ($categories as $c)
        <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
      @endforeach
    </select>
    <button class="md:col-span-3 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Filter</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">TITLE</th>
        <th class="p-3">AUTHOR</th>
        <th class="p-3">CATEGORY</th>
        <th class="p-3 text-center">SHELF</th>
        <th class="p-3 text-center">AVAILABLE</th>
        <th class="p-3 text-right">ACTION</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($books as $b)
        <tr class="hover:bg-gray-50">
          <td class="p-3 font-semibold text-navy">{{ $b->title }}</td>
          <td class="p-3 text-gray-600">{{ $b->author ?? '—' }}</td>
          <td class="p-3 text-xs"><span class="px-2 py-0.5 rounded-full bg-navy/10 text-navy font-semibold">{{ $b->category ?? '—' }}</span></td>
          <td class="p-3 text-center text-xs font-mono">{{ $b->shelf ?? '—' }}</td>
          <td class="p-3 text-center">
            @if ($b->available_copies > 0)
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">{{ $b->available_copies }} / {{ $b->total_copies }}</span>
            @else
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">0 / {{ $b->total_copies }}</span>
            @endif
          </td>
          <td class="p-3 text-right">
            <a href="{{ route('library.books.edit', $b) }}" class="text-xs font-semibold text-navy hover:text-gold">Edit</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-12 text-center text-gray-400">No books in catalog</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $books->links() }}</div>
@endsection
