@extends('layouts.public')
@section('title', 'Verify Code')
@section('content')

<section class="bg-navy text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-sm">PARENT PORTAL</div>
    <h1 class="text-2xl md:text-4xl font-extrabold mt-2">Enter Your Code</h1>
  </div>
</section>

<section class="max-w-md mx-auto px-4 py-12">
  @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-6 rounded-lg"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
  @endif
  @if (session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
  @endif

  <div class="card p-6 md:p-8" data-aos="fade-up">
    <form method="POST" action="{{ route('parent.verify-otp') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-semibold text-navy mb-2">6-Digit Code *</label>
        <input name="otp" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autofocus
               placeholder="123456" class="w-full min-h-[56px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none text-2xl text-center tracking-[0.5em] font-bold">
      </div>
      <button class="btn btn-gold w-full">✅ Verify & Log In</button>
    </form>

    <div class="mt-6 text-center text-xs text-gray-500">
      Didn't receive it? Check your SMS inbox. Code expires in 5 minutes.
    </div>

    <div class="mt-4 text-center">
      <a href="{{ route('parent.login') }}" class="text-sm text-navy underline">← Use a different number</a>
    </div>
  </div>
</section>

@endsection
