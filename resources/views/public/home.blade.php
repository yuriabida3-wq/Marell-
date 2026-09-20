@extends('layouts.public')
@section('title', 'Home')
@section('content')

{{-- HERO --}}
<section class="relative bg-navy text-white overflow-hidden">
  <div class="absolute inset-0 opacity-20 bg-[url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1600')] bg-cover bg-center"></div>
  <div class="relative max-w-7xl mx-auto px-4 py-20 md:py-32 text-center">
    <h1 class="text-3xl md:text-5xl lg:text-6xl font-extrabold leading-tight" data-aos="fade-up">
      Empowering <span class="text-gold">Tomorrow's Leaders</span>
    </h1>
    <p class="mt-6 text-base md:text-lg text-white/90 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
      Quality CBC education in the heart of Kenya. Nurturing discipline, excellence, and integrity since 2005.
    </p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center" data-aos="fade-up" data-aos-delay="200">
      <a href="/pay" class="btn btn-gold">💳 Pay Fees Online</a>
      <a href="/admissions" class="btn btn-outline">📝 Admissions</a>
      <a href="/results" class="btn btn-outline">📊 Check Results</a>
    </div>
  </div>
</section>

{{-- TRUST STRIP --}}
<section class="bg-white border-b">
  <div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    <div data-aos="fade-up"><div class="text-3xl md:text-4xl font-extrabold text-navy">98%</div><div class="text-xs md:text-sm text-gray-600 mt-1">KCSE Pass Rate</div></div>
    <div data-aos="fade-up" data-aos-delay="80"><div class="text-3xl md:text-4xl font-extrabold text-navy">1,200+</div><div class="text-xs md:text-sm text-gray-600 mt-1">Students</div></div>
    <div data-aos="fade-up" data-aos-delay="160"><div class="text-3xl md:text-4xl font-extrabold text-navy">45</div><div class="text-xs md:text-sm text-gray-600 mt-1">Qualified Teachers</div></div>
    <div data-aos="fade-up" data-aos-delay="240"><div class="text-3xl md:text-4xl font-extrabold text-navy">20</div><div class="text-xs md:text-sm text-gray-600 mt-1">Years of Excellence</div></div>
  </div>
</section>

{{-- DIRECTOR WELCOME --}}
<section class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
  <div data-aos="fade-up">
    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=800" alt="Director" class="rounded-2xl shadow-xl w-full h-80 object-cover">
  </div>
  <div data-aos="fade-up" data-aos-delay="100">
    <div class="text-gold font-bold tracking-widest text-sm mb-2">WELCOME MESSAGE</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mb-4">A Word from the Director</h2>
    <p class="text-gray-700 leading-relaxed mb-4">
      At Marell Academy, we believe every child is a leader in waiting. Our mission is to provide a holistic education that sharpens the mind, builds character, and inspires service.
    </p>
    <div class="border-l-4 border-gold pl-4">
      <div class="font-bold text-navy">Mr. James Wanyonyi</div>
      <div class="text-sm text-gray-600">Director &amp; Principal · KCSE Mean 8.2</div>
    </div>
  </div>
</section>

{{-- FEE PREVIEW --}}
<section class="bg-navy text-white py-14">
  <div class="max-w-3xl mx-auto px-4 text-center" data-aos="fade-up">
    <h2 class="text-2xl md:text-3xl font-extrabold mb-3">Check Your Fee Balance</h2>
    <p class="text-white/80 mb-6">Enter your child's admission number to view the current fee balance.</p>
    <form action="/pay" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
      <input name="adm" placeholder="e.g. MAR-2024-0001" required class="flex-1 min-h-[48px] rounded-xl px-4 text-gray-900 focus:outline-none focus:ring-4 focus:ring-gold/50">
      <button class="btn btn-gold">Check Now</button>
    </form>
  </div>
</section>

{{-- NEWS PREVIEW --}}
<section class="max-w-7xl mx-auto px-4 py-16">
  <div class="text-center mb-10">
    <div class="text-gold font-bold tracking-widest text-sm">LATEST UPDATES</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">News &amp; Events</h2>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @forelse ($news as $item)
      <article class="card overflow-hidden hover:shadow-2xl transition" data-aos="fade-up">
        <img src="{{ $item->image ?: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600' }}" class="w-full h-48 object-cover">
        <div class="p-5">
          <div class="text-xs text-gold font-semibold mb-1">{{ $item->created_at->format('d M Y') }}</div>
          <h3 class="font-bold text-navy text-lg mb-2">{{ $item->title }}</h3>
          <p class="text-sm text-gray-600 line-clamp-2">{{ $item->excerpt }}</p>
          <a href="/news/{{ $item->slug }}" class="inline-block mt-3 text-navy font-semibold hover:text-gold">Read more →</a>
        </div>
      </article>
    @empty
      @foreach ([['Sports Day 2024','Annual sports gala brings out the best in our athletes.'],['KCSE Excellence','Our candidates posted a stellar 98% pass rate.'],['New Science Lab','State-of-the-art lab now open for junior secondary.']] as $n)
        <article class="card overflow-hidden hover:shadow-2xl transition" data-aos="fade-up">
          <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600" class="w-full h-48 object-cover">
          <div class="p-5">
            <div class="text-xs text-gold font-semibold mb-1">{{ now()->format('d M Y') }}</div>
            <h3 class="font-bold text-navy text-lg mb-2">{{ $n[0] }}</h3>
            <p class="text-sm text-gray-600">{{ $n[1] }}</p>
            <span class="inline-block mt-3 text-navy font-semibold">Read more →</span>
          </div>
        </article>
      @endforeach
    @endforelse
  </div>
</section>

{{-- MAP --}}
<section class="w-full h-72 md:h-96">
  <iframe src="https://www.google.com/maps?q=Bungoma,Kenya&output=embed" class="w-full h-full border-0" loading="lazy"></iframe>
</section>

@endsection
