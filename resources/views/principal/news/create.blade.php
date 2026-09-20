@extends('layouts.admin')
@section('title', 'New Post')
@section('content')
<div class="mb-6"><a href="{{ route('principal.news.index') }}" class="text-xs text-gray-500">← News</a><h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">New Post</h1></div>
@if ($errors->any())<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>@endif
<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ route('principal.news.store') }}" class="space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TITLE *</label><input name="title" value="{{ old('title') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EXCERPT (short summary)</label><textarea name="excerpt" rows="2" maxlength="300" class="w-full rounded-xl border-2 border-gray-200 px-4 py-2 focus:border-gold focus:outline-none">{{ old('excerpt') }}</textarea></div>
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">BODY *</label><textarea name="body" rows="8" required class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:border-gold focus:outline-none">{{ old('body') }}</textarea></div>
    <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">IMAGE URL</label><input name="image" type="url" value="{{ old('image') }}" placeholder="https://..." class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="published" value="1" checked class="w-5 h-5"> Publish immediately</label>
    <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">📤 Publish Post</button>
  </form>
</div>
@endsection
