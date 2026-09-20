@extends('layouts.admin')
@section('title', 'Edit Post')
@section('content')
<div class="mb-6"><a href="{{ route('principal.news.index') }}" class="text-xs text-gray-500">← News</a><h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Edit Post</h1></div>
<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ route('principal.news.update', $news) }}" class="space-y-4">
    @csrf @method('PUT')
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TITLE *</label><input name="title" value="{{ old('title', $news->title) }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EXCERPT</label><textarea name="excerpt" rows="2" maxlength="300" class="w-full rounded-xl border-2 border-gray-200 px-4 py-2">{{ old('excerpt', $news->excerpt) }}</textarea></div>
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">BODY *</label><textarea name="body" rows="8" required class="w-full rounded-xl border-2 border-gray-200 px-4 py-3">{{ old('body', $news->body) }}</textarea></div>
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">IMAGE URL</label><input name="image" type="url" value="{{ old('image', $news->image) }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4"></div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="published" value="1" {{ $news->published ? 'checked' : '' }} class="w-5 h-5"> Published</label>
    <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">💾 Save</button>
  </form>
</div>
@endsection
