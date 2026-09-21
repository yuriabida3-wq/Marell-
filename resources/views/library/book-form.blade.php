@extends(role_layout())
@section('title', isset($book) ? 'Edit Book' : 'Add Book')
@section('content')
<div class="mb-6">
  <a href="{{ route('library.books') }}" class="text-xs text-gray-500">← Catalog</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ isset($book) ? 'Edit Book' : 'Add New Book' }}</h1>
</div>
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif
<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ isset($book) ? route('library.books.update', $book) : route('library.books.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf
    @if (isset($book)) @method('PUT') @endif

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TITLE *</label>
      <input name="title" value="{{ old('title', $book->title ?? '') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AUTHOR</label>
      <input name="author" value="{{ old('author', $book->author ?? '') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ISBN</label>
      <input name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CATEGORY</label>
      <input name="category" value="{{ old('category', $book->category ?? '') }}" placeholder="e.g. Fiction, Science, Kiswahili" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PUBLISHER</label>
      <input name="publisher" value="{{ old('publisher', $book->publisher ?? '') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">YEAR</label>
      <input name="year" type="number" value="{{ old('year', $book->year ?? '') }}" min="1900" max="2100" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TOTAL COPIES *</label>
      <input name="total_copies" type="number" min="1" value="{{ old('total_copies', $book->total_copies ?? 1) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SHELF</label>
      <input name="shelf" value="{{ old('shelf', $book->shelf ?? '') }}" placeholder="e.g. A-12" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div class="md:col-span-2 flex gap-3">
      <button class="flex-1 min-h-[48px] rounded-xl bg-gold text-navy font-bold">💾 Save Book</button>
      <a href="{{ route('library.books') }}" class="flex-1 min-h-[48px] rounded-xl bg-gray-100 text-gray-700 font-semibold text-center leading-[48px]">Cancel</a>
    </div>
  </form>
</div>
@endsection
