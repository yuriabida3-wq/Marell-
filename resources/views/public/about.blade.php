@extends('layouts.public')
@section('title', 'About Us')
@section('meta_description', 'Learn about Marell Academy — our history, mission, vision, values, and the teachers who make it happen.')

@section('content')

{{-- HERO --}}
<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">ABOUT US</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Our Story, Our Mission</h1>
    <p class="mt-4 text-white/80 max-w-2xl mx-auto">Two decades of shaping Kenya's brightest minds through discipline, innovation, and community.</p>
  </div>
</section>

{{-- DIRECTOR MESSAGE --}}
<section class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-3 gap-10 items-start">
  <div class="md:col-span-1" data-aos="fade-up">
    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=800" alt="Mr. James Wanyonyi" class="rounded-2xl shadow-xl w-full object-cover h-96">
    <div class="mt-4 text-center md:text-left">
      <div class="font-bold text-navy text-lg">Mr. James Wanyonyi</div>
      <div class="text-sm text-gray-600">Director &amp; Principal</div>
      <div class="text-xs text-gold font-semibold mt-1">KCSE Mean 8.2</div>
    </div>
  </div>
  <div class="md:col-span-2" data-aos="fade-up" data-aos-delay="100">
    <div class="text-gold font-bold tracking-widest text-sm mb-2">DIRECTOR'S MESSAGE</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mb-4">A Foundation Built on Character</h2>
    <p class="text-gray-700 leading-relaxed mb-4">
      When we opened our gates in 2005, we had one simple dream — to build a school where academic excellence and moral integrity walk hand in hand. Today, that dream lives in the over 1,200 alumni who carry the Marell badge into universities, careers, and communities across Kenya.
    </p>
    <p class="text-gray-700 leading-relaxed mb-4">
      Our teachers are not just instructors; they are mentors, coaches, and role models. Our classrooms are not just spaces; they are launchpads. And every child who walks through our gates is not a number — they are a promise.
    </p>
    <p class="text-gray-700 leading-relaxed italic border-l-4 border-gold pl-4">
      "We don't just prepare students for exams. We prepare them for life."
    </p>
  </div>
</section>

{{-- MISSION / VISION / VALUES --}}
<section class="bg-gray-100 py-16">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-10">
      <div class="text-gold font-bold tracking-widest text-sm">WHAT DRIVES US</div>
      <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Mission · Vision · Values</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card p-8 text-center hover:shadow-2xl transition" data-aos="fade-up">
        <div class="w-16 h-16 mx-auto rounded-full bg-navy/10 flex items-center justify-center mb-4 text-3xl">🎯</div>
        <h3 class="font-bold text-navy text-xl mb-3">Our Mission</h3>
        <p class="text-sm text-gray-600 leading-relaxed">To provide holistic, learner-centered CBC education that develops competent, confident, and ethical citizens ready to serve Kenya and the world.</p>
      </div>
      <div class="card p-8 text-center hover:shadow-2xl transition" data-aos="fade-up" data-aos-delay="100">
        <div class="w-16 h-16 mx-auto rounded-full bg-gold/20 flex items-center justify-center mb-4 text-3xl">🌟</div>
        <h3 class="font-bold text-navy text-xl mb-3">Our Vision</h3>
        <p class="text-sm text-gray-600 leading-relaxed">To be the leading private school in Western Kenya — a beacon of academic excellence, innovation, and character formation.</p>
      </div>
      <div class="card p-8 text-center hover:shadow-2xl transition" data-aos="fade-up" data-aos-delay="200">
        <div class="w-16 h-16 mx-auto rounded-full bg-navy/10 flex items-center justify-center mb-4 text-3xl">💎</div>
        <h3 class="font-bold text-navy text-xl mb-3">Our Core Values</h3>
        <p class="text-sm text-gray-600 leading-relaxed">Discipline · Integrity · Excellence · Respect · Service · Innovation</p>
      </div>
    </div>
  </div>
</section>

{{-- TEACHERS GRID --}}
<section class="max-w-7xl mx-auto px-4 py-16">
  <div class="text-center mb-10">
    <div class="text-gold font-bold tracking-widest text-sm">OUR TEAM</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Meet Our Teachers</h2>
    <p class="text-gray-600 mt-2 max-w-xl mx-auto">Passionate educators dedicated to unlocking every learner's potential.</p>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
    @php
      $teachers = [
        ['Jane Wanjiru', 'Mathematics', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400'],
        ['Peter Omondi', 'Sciences', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400'],
        ['Grace Mueni', 'English &amp; Literature', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400'],
        ['Samuel Kariuki', 'Kiswahili', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400'],
        ['Mary Achieng', 'Social Studies', 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?w=400'],
        ['David Mutua', 'ICT &amp; Innovation', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400'],
      ];
    @endphp
    @foreach ($teachers as $t)
      <div class="card overflow-hidden text-center hover:shadow-2xl transition" data-aos="fade-up">
        <img src="{{ $t[2] }}" alt="{{ $t[0] }}" class="w-full h-48 md:h-56 object-cover">
        <div class="p-4">
          <div class="font-bold text-navy">{!! $t[0] !!}</div>
          <div class="text-xs text-gray-500 mt-1">{!! $t[1] !!}</div>
        </div>
      </div>
    @endforeach
  </div>
</section>

{{-- CTA --}}
<section class="bg-navy text-white py-14">
  <div class="max-w-3xl mx-auto px-4 text-center" data-aos="fade-up">
    <h2 class="text-2xl md:text-3xl font-extrabold mb-3">Ready to Join the Marell Family?</h2>
    <p class="text-white/80 mb-6">Applications for 2025 intake are now open.</p>
    <a href="/admissions" class="btn btn-gold">Apply Now</a>
  </div>
</section>

@include('public._related')
@endsection
