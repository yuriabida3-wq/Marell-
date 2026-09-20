@extends('layouts.public')
@section('title', $post->title)
@section('content')
<section class="bg-navy text-white py-12 md:py-16">
  <div class="max-w-4xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-xs">{{ $post->created_at->format('d M Y') }}</div>
    <h1 class="text-2xl md:text-4xl font-extrabold mt-2">{{ $post->title }}</h1>
  </div>
</section>
<article class="max-w-3xl mx-auto px-4 py-12">
  @if ($post->image)
    <img src="{{ $post->image }}" class="w-full h-72 object-cover rounded-2xl shadow-xl mb-8">
  @endif
  <div class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $post->body }}</div>
  <div class="mt-12 pt-8 border-t">
    <a href="/news" class="text-navy font-semibold hover:text-gold">← Back to news</a>
  </div>

  @if ($related->count())
    <div class="mt-12">
      <h2 class="font-extrabold text-navy text-xl mb-4">Related</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($related as $r)
          <a href="/news/{{ $r->slug }}" class="card p-4 hover:shadow-lg transition">
            <div class="text-xs text-gold font-semibold">{{ $r->created_at->format('d M Y') }}</div>
            <div class="font-bold text-navy mt-1">{{ $r->title }}</div>
          </a>
        @endforeach
      </div>
    </div>
  @endif
</article>
@endsection
