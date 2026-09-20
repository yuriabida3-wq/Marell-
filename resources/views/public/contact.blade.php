@extends('layouts.public')
@section('title', 'Contact Us')
@section('content')
<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">CONTACT</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Get in Touch</h1>
    <p class="mt-4 text-white/80 max-w-2xl mx-auto">We'd love to hear from you. Visit, call, or send a message below.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 gap-8">
  <div class="space-y-4">
    <div class="card p-6 flex items-start gap-4" data-aos="fade-up">
      <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center text-xl flex-shrink-0">📍</div>
      <div><div class="font-bold text-navy">Visit Us</div><div class="text-sm text-gray-700 mt-1">Marell Academy<br>Kanduyi Road<br>Bungoma, Kenya</div></div>
    </div>
    <div class="card p-6 flex items-start gap-4" data-aos="fade-up" data-aos-delay="80">
      <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center text-xl flex-shrink-0">📞</div>
      <div><div class="font-bold text-navy">Call Us</div><div class="text-sm text-gray-700 mt-1"><a href="tel:+254700000000" class="hover:text-gold">+254 700 000 000</a><br><a href="tel:+254711111111" class="hover:text-gold">+254 711 111 111</a></div></div>
    </div>
    <div class="card p-6 flex items-start gap-4" data-aos="fade-up" data-aos-delay="160">
      <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center text-xl flex-shrink-0">✉️</div>
      <div><div class="font-bold text-navy">Email Us</div><div class="text-sm text-gray-700 mt-1"><a href="mailto:info@marell.ac.ke" class="hover:text-gold">info@marell.ac.ke</a><br><a href="mailto:admissions@marell.ac.ke" class="hover:text-gold">admissions@marell.ac.ke</a></div></div>
    </div>
    <div class="card p-6 flex items-start gap-4" data-aos="fade-up" data-aos-delay="240">
      <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center text-xl flex-shrink-0">🕐</div>
      <div><div class="font-bold text-navy">Office Hours</div><div class="text-sm text-gray-700 mt-1">Mon–Fri: 7:30 AM – 5:00 PM<br>Sat: 8:00 AM – 1:00 PM<br>Sun &amp; Holidays: Closed</div></div>
    </div>
  </div>

  <div class="card p-6 md:p-8" data-aos="fade-up">
    <h2 class="text-2xl font-extrabold text-navy mb-6">Send a Message</h2>
    @if (session('success'))
      <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-6 rounded-lg"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
    @endif
    @if ($errors->any())
      <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg"><ul class="text-sm text-red-600 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
    @endif
    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
      @csrf
      <div><label class="block text-sm font-semibold text-navy mb-2">Your Name *</label><input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold text-navy mb-2">Email</label><input name="email" type="email" value="{{ old('email') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
        <div><label class="block text-sm font-semibold text-navy mb-2">Phone</label><input name="phone" value="{{ old('phone') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
      </div>
      <div><label class="block text-sm font-semibold text-navy mb-2">Subject</label><input name="subject" value="{{ old('subject') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
      <div><label class="block text-sm font-semibold text-navy mb-2">Message *</label><textarea name="message" rows="5" required class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:border-gold focus:outline-none">{{ old('message') }}</textarea></div>
      <button class="btn btn-gold w-full">📩 Send Message</button>
    </form>
  </div>
</section>

<section class="w-full h-72 md:h-96 mt-4">
  <iframe src="https://www.google.com/maps?q=Bungoma,Kenya&output=embed" class="w-full h-full border-0" loading="lazy"></iframe>
</section>
@endsection
