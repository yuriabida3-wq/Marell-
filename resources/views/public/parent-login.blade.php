@extends('layouts.public')
@section('title', 'Parent Portal Login')
@section('content')

<section class="bg-navy text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">PARENT PORTAL</div>
    <h1 class="text-2xl md:text-4xl font-extrabold mt-2">Welcome Back</h1>
    <p class="mt-3 text-white/80 max-w-xl mx-auto text-sm md:text-base">Log in with your phone number to view fee balances, results, and payment history.</p>
  </div>
</section>

<section class="max-w-md mx-auto px-4 py-12">
  @if (session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
  @endif

  <div class="card p-6 md:p-8" data-aos="fade-up">
    <form method="POST" action="{{ route('parent.send-otp') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-semibold text-navy mb-2">Registered Phone Number *</label>
        <input name="phone" value="{{ old('phone') }}" required placeholder="07XXXXXXXX" autofocus
               class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none text-lg">
        <div class="text-xs text-gray-500 mt-2">Use the phone number you gave the school during admission.</div>
      </div>

      <button class="btn btn-gold w-full">📱 Send Login Code</button>
    </form>

    <div class="mt-6 text-center text-xs text-gray-500">
      🔒 We'll send a 6-digit code via SMS. No password needed.
    </div>
  </div>

  <div class="text-center mt-8 text-sm text-gray-600">
    <p>Not registered yet? <a href="/admissions" class="text-navy font-semibold">Apply online</a></p>
  </div>
</section>

@endsection
