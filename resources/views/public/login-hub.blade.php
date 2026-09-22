@extends('layouts.public')
@section('title', 'Login Portal')
@section('content')

<section class="bg-navy text-white py-12 md:py-16">
  <div class="max-w-6xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">ALL PORTALS</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Login Center</h1>
    <p class="mt-3 text-white/80 max-w-xl mx-auto text-sm md:text-base">Choose your role to continue</p>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-12">

  {{-- STAFF LOGINS --}}
  <div class="mb-10">
    <div class="text-center mb-6">
      <div class="text-gold font-bold tracking-widest text-xs">STAFF PORTAL</div>
      <h2 class="text-2xl font-extrabold text-navy mt-1">Staff Access</h2>
      <p class="text-sm text-gray-500 mt-1">Principal · DOS · Bursar · Teacher</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <a href="/login" class="card p-6 hover:shadow-xl transition flex items-center gap-4 border-l-4 border-navy">
        <div class="w-14 h-14 rounded-full bg-navy text-white flex items-center justify-center text-2xl flex-shrink-0">👨‍💼</div>
        <div class="flex-1">
          <div class="font-bold text-navy text-lg">Staff Login</div>
          <div class="text-xs text-gray-500 mt-1">Email + password · All staff roles</div>
        </div>
        <div class="text-navy text-2xl">→</div>
      </a>

      <a href="/security/login" class="card p-6 hover:shadow-xl transition flex items-center gap-4 border-l-4 border-gold">
        <div class="w-14 h-14 rounded-full bg-gold text-navy flex items-center justify-center text-2xl flex-shrink-0">🛡️</div>
        <div class="flex-1">
          <div class="font-bold text-navy text-lg">Security Guard</div>
          <div class="text-xs text-gray-500 mt-1">Phone + 4-digit PIN</div>
        </div>
        <div class="text-navy text-2xl">→</div>
      </a>
    </div>

    {{-- DASHBOARD QUICK LINKS --}}
    <div class="bg-white rounded-2xl p-5 shadow">
      <div class="text-xs font-bold text-navy tracking-widest mb-3">QUICK LINKS (AFTER LOGIN)</div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
        <a href="/principal" class="p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
          <div class="text-2xl">👑</div>
          <div class="text-xs font-semibold mt-1">Principal</div>
        </a>
        <a href="/dos" class="p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
          <div class="text-2xl">📚</div>
          <div class="text-xs font-semibold mt-1">DOS</div>
        </a>
        <a href="/bursar" class="p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
          <div class="text-2xl">💰</div>
          <div class="text-xs font-semibold mt-1">Bursar</div>
        </a>
        <a href="/teacher" class="p-3 rounded-xl bg-gray-50 hover:bg-navy hover:text-white transition">
          <div class="text-2xl">👨‍🏫</div>
          <div class="text-xs font-semibold mt-1">Teacher</div>
        </a>
      </div>
    </div>
  </div>

  {{-- PARENT LOGIN --}}
  <div class="mb-10">
    <div class="text-center mb-6">
      <div class="text-gold font-bold tracking-widest text-xs">PARENT PORTAL</div>
      <h2 class="text-2xl font-extrabold text-navy mt-1">Parent Access</h2>
      <p class="text-sm text-gray-500 mt-1">No password needed — SMS code only</p>
    </div>

    <a href="/parent/login" class="card p-6 hover:shadow-xl transition flex items-center gap-4 border-l-4 border-gold max-w-3xl mx-auto">
      <div class="w-14 h-14 rounded-full bg-gold text-navy flex items-center justify-center text-2xl flex-shrink-0">👨‍👩‍👧</div>
      <div class="flex-1">
        <div class="font-bold text-navy text-lg">Parent Login</div>
        <div class="text-xs text-gray-500 mt-1">Enter phone number → Get SMS code → Login</div>
      </div>
      <div class="text-navy text-2xl">→</div>
    </a>
  </div>

  {{-- PUBLIC ACCESS --}}
  <div>
    <div class="text-center mb-6">
      <div class="text-gold font-bold tracking-widest text-xs">NO LOGIN NEEDED</div>
      <h2 class="text-2xl font-extrabold text-navy mt-1">Public Pages</h2>
      <p class="text-sm text-gray-500 mt-1">Available to everyone</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <a href="/" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">🏠</div>
        <div class="font-bold text-navy text-sm">Home</div>
      </a>
      <a href="/results" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">📊</div>
        <div class="font-bold text-navy text-sm">Check Results</div>
      </a>
      <a href="/pay" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">💳</div>
        <div class="font-bold text-navy text-sm">Pay Fees</div>
      </a>
      <a href="/library-catalog" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">📚</div>
        <div class="font-bold text-navy text-sm">Library</div>
      </a>
      <a href="/admissions" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">📝</div>
        <div class="font-bold text-navy text-sm">Apply</div>
      </a>
      <a href="/timetable" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">📅</div>
        <div class="font-bold text-navy text-sm">Timetable</div>
      </a>
      <a href="/report" class="card p-4 text-center hover:shadow-lg transition border-2 border-gold">
        <div class="text-3xl mb-1">🕊️</div>
        <div class="font-bold text-navy text-sm">Report Concern</div>
      </a>
      <a href="/verify-receipt/REC-2026-00003" class="card p-4 text-center hover:shadow-lg transition">
        <div class="text-3xl mb-1">✅</div>
        <div class="font-bold text-navy text-sm">Verify Receipt</div>
      </a>
    </div>
  </div>

  {{-- DEMO CREDENTIALS (only shows in demo/local) --}}
  @if (config('app.env') !== 'production' || config('services.demo_mode', false))
    <div class="mt-12 bg-gold/10 border-l-4 border-gold rounded-xl p-5">
      <div class="font-bold text-navy mb-3">🔑 Demo Credentials</div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div class="bg-white rounded-lg p-3">
          <div class="text-xs text-gray-500">Principal</div>
          <div class="font-mono text-xs text-navy">admin@marell.ac.ke / 123456</div>
        </div>
        <div class="bg-white rounded-lg p-3">
          <div class="text-xs text-gray-500">DOS</div>
          <div class="font-mono text-xs text-navy">dos@marell.ac.ke / 123456</div>
        </div>
        <div class="bg-white rounded-lg p-3">
          <div class="text-xs text-gray-500">Bursar</div>
          <div class="font-mono text-xs text-navy">yuriabida3@gmail.com / 123456</div>
        </div>
        <div class="bg-white rounded-lg p-3">
          <div class="text-xs text-gray-500">Teacher</div>
          <div class="font-mono text-xs text-navy">jane@marell.ac.ke / 123456</div>
        </div>
        <div class="bg-white rounded-lg p-3">
          <div class="text-xs text-gray-500">Security Guard</div>
          <div class="font-mono text-xs text-navy">0700900001 / PIN 1234</div>
        </div>
        <div class="bg-white rounded-lg p-3">
          <div class="text-xs text-gray-500">Parent (OTP)</div>
          <div class="font-mono text-xs text-navy">254720001111</div>
        </div>
      </div>
    </div>
  @endif
</section>
@endsection
