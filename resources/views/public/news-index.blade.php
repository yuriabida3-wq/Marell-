@extends('layouts.public')
@section('title', 'News')
@section('content')
<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">NEWS &amp; EVENTS</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Latest from Marell</h1>
  </div>
</section>
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @forelse ($news as $n)
      <article class="card overflow-hidden hover:shadow-2xl transition" data-aos="fade-up">
        <img src="{{ $n->image ?: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600' }}" class="w-full h-48 object-cover">
        <div class="p-5">
          <div class="text-xs text-gold font-semibold mb-1">{{ $n->created_at->format('d M Y') }}</div>
          <h3 class="font-bold text-navy text-lg mb-2">{{ $n->title }}</h3>
          <p class="text-sm text-gray-600 line-clamp-2">{{ $n->excerpt }}</p>
          <a href="/news/{{ $n->slug }}" class="inline-block mt-3 text-navy font-semibold hover:text-gold">Read more →</a>
        </div>
      </article>
    @empty
      <div class="md:col-span-3 text-center py-20 text-gray-400"><div class="text-4xl mb-2">📭</div>No news yet</div>
    @endforelse
  </div>
  <div class="mt-8">{{ $news->links() }}</div>
</section>
@endsection
