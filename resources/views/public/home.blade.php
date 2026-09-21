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
    <div data-aos="fade-up" data-aos-delay="80"><div class="text-3xl md:text-4xl font-extrabold text-navy">1,200+</div><div class="text-xs md:text-sm text-gray-600 mt-1">Alumni</div></div>
    <div data-aos="fade-up" data-aos-delay="160"><div class="text-3xl md:text-4xl font-extrabold text-navy">45</div><div class="text-xs md:text-sm text-gray-600 mt-1">Qualified Teachers</div></div>
    <div data-aos="fade-up" data-aos-delay="240"><div class="text-3xl md:text-4xl font-extrabold text-navy">20</div><div class="text-xs md:text-sm text-gray-600 mt-1">Years of Excellence</div></div>
  </div>
</section>

{{-- RECOVERED BADGE --}}
<section class="bg-gradient-to-r from-gold to-yellow-500 py-8">
  <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-center gap-4 text-navy text-center md:text-left">
    <div class="text-5xl">🏆</div>
    <div>
      <div class="font-extrabold text-2xl">Recovered KES 340,000 in 7 days</div>
      <div class="text-sm font-semibold">Using M-Pesa STK Push + automated SMS reminders</div>
    </div>
    <a href="/pay" class="btn btn-navy">Try it →</a>
  </div>
</section>

{{-- LIVE COUNTERS --}}
<section class="bg-white py-14">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-10" data-aos="fade-up">
      <div class="text-gold font-bold tracking-widest text-sm">LIVE STATS</div>
      <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Marell by the Numbers</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-navy rounded-2xl p-6 text-center text-white" data-aos="fade-up">
        <div class="text-4xl md:text-5xl font-extrabold text-gold">{{ \App\Models\Student::where("status","active")->count() }}+</div>
        <div class="text-xs md:text-sm text-white/80 mt-2">Active Students</div>
      </div>
      <div class="bg-gold rounded-2xl p-6 text-center text-navy" data-aos="fade-up" data-aos-delay="80">
        <div class="text-4xl md:text-5xl font-extrabold">{{ \App\Models\Result::avg("marks") ? round(\App\Models\Result::avg("marks")) : 0 }}%</div>
        <div class="text-xs md:text-sm font-semibold mt-2">Average Score</div>
      </div>
      <div class="bg-navy rounded-2xl p-6 text-center text-white" data-aos="fade-up" data-aos-delay="160">
        <div class="text-4xl md:text-5xl font-extrabold text-gold">{{ \App\Models\User::role("teacher")->count() }}</div>
        <div class="text-xs md:text-sm text-white/80 mt-2">Qualified Teachers</div>
      </div>
      <div class="bg-white border-4 border-navy rounded-2xl p-6 text-center" data-aos="fade-up" data-aos-delay="240">
        <div class="text-4xl md:text-5xl font-extrabold text-navy">{{ \App\Models\Classroom::where("active", true)->count() }}</div>
        <div class="text-xs md:text-sm text-gray-600 mt-2">Classes &amp; Streams</div>
      </div>
    </div>
  </div>
</section>

{{-- DIRECTOR --}}
<section class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
  <div data-aos="fade-up">
    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=800" alt="Director" class="rounded-2xl shadow-xl w-full h-80 object-cover">
  </div>
  <div data-aos="fade-up" data-aos-delay="100">
    <div class="text-gold font-bold tracking-widest text-sm mb-2">WELCOME MESSAGE</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mb-4">A Word from the Director</h2>
    <p class="text-gray-700 leading-relaxed mb-4">At Marell Academy, we believe every child is a leader in waiting. Our mission is to provide a holistic education that sharpens the mind, builds character, and inspires service.</p>
    <div class="border-l-4 border-gold pl-4">
      <div class="font-bold text-navy">Mr. James Wanyonyi</div>
      <div class="text-sm text-gray-600">Director &amp; Principal · KCSE Mean 8.2</div>
    </div>
  </div>
</section>

{{-- TESTIMONIALS --}}
<section class="bg-gray-100 py-14">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-10" data-aos="fade-up">
      <div class="text-gold font-bold tracking-widest text-sm">WHAT PARENTS SAY</div>
      <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Trusted by 300+ Families</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card p-6" data-aos="fade-up">
        <div class="text-gold text-2xl mb-2">★★★★★</div>
        <p class="text-sm text-gray-700 italic">"I pay from Nairobi at 10pm. Receipt on WhatsApp in 5 seconds. No more calling the bursar."</p>
        <div class="mt-4 text-sm font-bold text-navy">Mrs. Faith W., Parent</div>
      </div>
      <div class="card p-6" data-aos="fade-up" data-aos-delay="80">
        <div class="text-gold text-2xl mb-2">★★★★★</div>
        <p class="text-sm text-gray-700 italic">"As Director, I now see every shilling live. Defaulters no longer hidden until closing day."</p>
        <div class="mt-4 text-sm font-bold text-navy">Mr. James W., Director</div>
      </div>
      <div class="card p-6" data-aos="fade-up" data-aos-delay="160">
        <div class="text-gold text-2xl mb-2">★★★★★</div>
        <p class="text-sm text-gray-700 italic">"Report cards in 1 click. My teachers used to spend 2 days — now it takes 5 seconds."</p>
        <div class="mt-4 text-sm font-bold text-navy">Mrs. Mary N., DOS</div>
      </div>
    </div>
  </div>
</section>

{{-- HOW IT WORKS --}}
<section class="bg-navy text-white py-14">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-10" data-aos="fade-up">
      <div class="text-gold font-bold tracking-widest text-sm">HOW IT WORKS</div>
      <h2 class="text-2xl md:text-3xl font-extrabold mt-1">Pay in 30 Seconds</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
      <div data-aos="fade-up"><div class="w-16 h-16 mx-auto rounded-full bg-gold text-navy flex items-center justify-center text-2xl font-extrabold mb-4">1</div><div class="font-bold">Enter ADM</div><div class="text-sm text-white/70 mt-1">On /pay page</div></div>
      <div data-aos="fade-up" data-aos-delay="80"><div class="w-16 h-16 mx-auto rounded-full bg-gold text-navy flex items-center justify-center text-2xl font-extrabold mb-4">2</div><div class="font-bold">Enter Amount</div><div class="text-sm text-white/70 mt-1">Any amount</div></div>
      <div data-aos="fade-up" data-aos-delay="160"><div class="w-16 h-16 mx-auto rounded-full bg-gold text-navy flex items-center justify-center text-2xl font-extrabold mb-4">3</div><div class="font-bold">Approve M-Pesa</div><div class="text-sm text-white/70 mt-1">Enter your PIN</div></div>
      <div data-aos="fade-up" data-aos-delay="240"><div class="w-16 h-16 mx-auto rounded-full bg-gold text-navy flex items-center justify-center text-2xl font-extrabold mb-4">4</div><div class="font-bold">Instant Receipt</div><div class="text-sm text-white/70 mt-1">SMS + PDF</div></div>
    </div>
    <div class="text-center mt-10"><a href="/pay" class="btn btn-gold">💳 Pay Fees Now</a></div>
  </div>
</section>

{{-- FEE CHECK --}}
<section class="bg-white py-14">
  <div class="max-w-3xl mx-auto px-4 text-center" data-aos="fade-up">
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mb-3">Check Your Fee Balance</h2>
    <p class="text-gray-600 mb-6">Enter your child's admission number.</p>
    <form action="/pay" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
      <input name="adm" placeholder="e.g. MAR-2024-0001" required class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:outline-none focus:border-gold">
      <button class="btn btn-gold">Check Now</button>
    </form>
  </div>
</section>

{{-- NEWS --}}
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
      <div class="md:col-span-3 text-center py-12 text-gray-400">No news yet</div>
    @endforelse
  </div>
</section>

{{-- FAQ --}}
<section class="bg-gray-50 py-14">
  <div class="max-w-3xl mx-auto px-4">
    <div class="text-center mb-10" data-aos="fade-up">
      <div class="text-gold font-bold tracking-widest text-sm">PARENTS ASK</div>
      <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Frequently Asked</h2>
    </div>
    <div class="space-y-3">
      <details class="card p-5 group cursor-pointer"><summary class="font-semibold text-navy flex justify-between list-none"><span>How do I check my child's fee balance?</span><span class="text-gold text-2xl group-open:rotate-45 transition">+</span></summary><p class="mt-3 text-sm text-gray-700">Log into the <a href="/parent/login" class="text-navy underline">Parent Portal</a> with your phone number — OTP arrives in seconds. No password needed.</p></details>
      <details class="card p-5 group cursor-pointer"><summary class="font-semibold text-navy flex justify-between list-none"><span>Can I pay fees at night or on weekends?</span><span class="text-gold text-2xl group-open:rotate-45 transition">+</span></summary><p class="mt-3 text-sm text-gray-700">Yes. M-Pesa STK Push works 24/7. Pay at 11pm from anywhere. Receipt arrives instantly via SMS.</p></details>
      <details class="card p-5 group cursor-pointer"><summary class="font-semibold text-navy flex justify-between list-none"><span>Is my receipt genuine?</span><span class="text-gold text-2xl group-open:rotate-45 transition">+</span></summary><p class="mt-3 text-sm text-gray-700">Every receipt has a QR code. Scan it with your phone — opens our official verification page.</p></details>
      <details class="card p-5 group cursor-pointer"><summary class="font-semibold text-navy flex justify-between list-none"><span>When are results released?</span><span class="text-gold text-2xl group-open:rotate-45 transition">+</span></summary><p class="mt-3 text-sm text-gray-700">Results are SMS'd to every parent and posted on the Parent Portal.</p></details>
      <details class="card p-5 group cursor-pointer"><summary class="font-semibold text-navy flex justify-between list-none"><span>How do I report a concern?</span><span class="text-gold text-2xl group-open:rotate-45 transition">+</span></summary><p class="mt-3 text-sm text-gray-700">Use our <a href="/report" class="text-navy underline">anonymous report box</a>. Messages go directly to the Director.</p></details>
    </div>
  </div>
</section>

{{-- RELATED --}}
@include('public._related')

{{-- MAP --}}
<section class="w-full h-72 md:h-96">
  <iframe src="https://www.google.com/maps?q=Bungoma,Kenya&output=embed" class="w-full h-full border-0" loading="lazy"></iframe>
</section>

@endsection
