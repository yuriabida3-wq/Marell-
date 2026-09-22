<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#0B3D91">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Marell">

<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Marell Academy') — Empowering Tomorrow's Leaders</title>
<meta name="description" content="@yield('meta_description', 'Marell Academy — Quality CBC education in Kenya. Pay fees online, check results, apply online.')">

<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: { navy: '#0B3D91', gold: '#D4AF37' },
      fontFamily: { poppins: ['Poppins','sans-serif'], inter: ['Inter','sans-serif'] }
    }
  }
}
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

<style>
  body { font-family: 'Inter', sans-serif; color: #1f2937; }
  h1,h2,h3,h4 { font-family: 'Poppins', sans-serif; font-weight: 700; }
  .btn { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: 0 1.25rem; border-radius: .75rem; font-weight: 600; transition: all .2s; }
  .btn-gold { background: #D4AF37; color: #0B3D91; }
  .btn-gold:hover { transform: scale(1.05); box-shadow: 0 10px 25px rgba(212,175,55,.4); }
  .btn-navy { background: #0B3D91; color: #fff; }
  .btn-navy:hover { transform: scale(1.05); }
  .btn-outline { border: 2px solid #fff; color: #fff; }
  .btn-outline:hover { background: #fff; color: #0B3D91; }
  .card { border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); background: #fff; }
  .blur-nav { backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); background: rgba(11,61,145,.85); }
</style>
@stack('head')
</head>
<body class="bg-gray-50">

{{-- HEADER --}}
<header class="fixed top-0 left-0 right-0 z-40 blur-nav text-white shadow-lg">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2">
      <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-lg">M</div>
      <div class="leading-tight">
        <div class="font-poppins font-extrabold text-lg">MARELL</div>
        <div class="text-[10px] tracking-widest text-gold">ACADEMY</div>
      </div>
    </a>

    <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
      <a href="/" class="hover:text-gold transition">Home</a>
      <a href="/about" class="hover:text-gold transition">About</a>
      <a href="/academics" class="hover:text-gold transition">Academics</a>
      <a href="/admissions" class="hover:text-gold transition">Admissions</a>
      <a href="/fees" class="hover:text-gold transition">Fees</a>
      <a href="/results" class="hover:text-gold transition">Results</a>
      <a href="/news" class="hover:text-gold transition">News</a>
      <a href="/timetable" class="hover:text-gold transition">Timetable</a>
      <a href="/contact" class="hover:text-gold transition">Contact</a>
      <div class="relative group">
        <button class="hover:text-gold transition flex items-center gap-1">Portal <span class="text-xs">▾</span></button>
        <div class="absolute right-0 top-full pt-2 hidden group-hover:block">
          <div class="bg-white rounded-xl shadow-xl py-2 min-w-[200px] text-navy">
            <a href="/parent/login" class="block px-4 py-2 hover:bg-gold/10 text-sm">🔐 Parent Login (OTP)</a>
            <a href="/pay" class="block px-4 py-2 hover:bg-gold/10 text-sm">💳 Pay Fees Online</a>
            <a href="/results" class="block px-4 py-2 hover:bg-gold/10 text-sm">📊 Check Results</a>
            <a href="/verify-receipt/REC-2026-00003" class="block px-4 py-2 hover:bg-gold/10 text-sm">✓ Verify Receipt</a>
            <a href="/report" class="block px-4 py-2 hover:bg-gold/10 text-sm">🕊️ Report Anonymously</a>
            <div class="border-t my-1"></div>
            <a href="/login" class="block px-4 py-2 hover:bg-gold/10 text-sm text-gray-500">👨‍💼 Staff Login</a>
          </div>
        </div>
      </div>
      <a href="/pay" class="btn btn-gold text-sm !min-h-[42px] !py-1.5 !px-4">Pay Fees</a>
    </nav>

    <button id="hamburger" class="md:hidden p-2" aria-label="Menu">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
  </div>
</header>

{{-- MOBILE MENU (slides from right) --}}
<div id="mobileOverlay" class="fixed inset-0 bg-black/50 z-50 hidden"></div>
<aside id="mobileMenu" class="fixed top-0 right-0 h-full w-72 max-w-[85%] bg-navy text-white z-50 translate-x-full transition-transform duration-300 flex flex-col">
  <div class="flex items-center justify-between p-4 border-b border-white/10">
    <span class="font-poppins font-bold">Menu</span>
    <button id="closeMenu" class="p-2" aria-label="Close">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </div>
  <nav class="flex-1 overflow-y-auto p-4 flex flex-col gap-1 text-base">
    <a href="/" class="py-3 px-3 rounded-lg hover:bg-white/10">Home</a>
    <a href="/about" class="py-3 px-3 rounded-lg hover:bg-white/10">About</a>
    <a href="/academics" class="py-3 px-3 rounded-lg hover:bg-white/10">Academics</a>
    <a href="/admissions" class="py-3 px-3 rounded-lg hover:bg-white/10">Admissions</a>
    <a href="/fees" class="py-3 px-3 rounded-lg hover:bg-white/10">Fees</a>
    <a href="/results" class="py-3 px-3 rounded-lg hover:bg-white/10">Results</a>
    <a href="/news" class="py-3 px-3 rounded-lg hover:bg-white/10">News</a>
    <a href="/timetable" class="py-3 px-3 rounded-lg hover:bg-white/10">Timetable</a>
    <a href="/contact" class="py-3 px-3 rounded-lg hover:bg-white/10">Contact</a>

    <div class="mt-4 pt-4 border-t border-white/10">
      <div class="text-[10px] tracking-widest text-gold font-bold px-3 mb-2">PARENT PORTAL</div>
      <a href="/parent/login" class="py-2 px-3 rounded-lg hover:bg-white/10 flex items-center gap-2">🔐 Login (OTP)</a>
      <a href="/pay" class="py-2 px-3 rounded-lg hover:bg-white/10 flex items-center gap-2">💳 Pay Fees</a>
      <a href="/results" class="py-2 px-3 rounded-lg hover:bg-white/10 flex items-center gap-2">📊 Check Results</a>
      <a href="/report" class="py-2 px-3 rounded-lg hover:bg-white/10 flex items-center gap-2">🕊️ Report Anonymously</a>
    </div>

    <a href="/pay" class="btn btn-gold mt-4 w-full">Pay Fees</a>
    <a href="/login" class="btn btn-outline mt-2 w-full text-sm">Staff Login</a>
  </nav>
</aside>

{{-- MAIN --}}
<main class="pt-16">@yield('content')</main>

{{-- FOOTER --}}
<footer class="bg-navy text-white mt-20">
  <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
    <div>
      <div class="flex items-center gap-2 mb-4">
        <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center font-bold text-navy">M</div>
        <div><div class="font-poppins font-extrabold">MARELL</div><div class="text-[10px] tracking-widest text-gold">ACADEMY</div></div>
      </div>
      <p class="text-sm text-white/80">Empowering Tomorrow's Leaders — Quality CBC education rooted in discipline, excellence, and integrity.</p>
    </div>
    <div>
      <h4 class="font-bold mb-3 text-gold">Quick Links</h4>
      <ul class="space-y-2 text-sm text-white/80">
        <li><a href="/admissions" class="hover:text-gold">Admissions</a></li>
        <li><a href="/fees" class="hover:text-gold">Fee Structure</a></li>
        <li><a href="/results" class="hover:text-gold">Check Results</a></li>
        <li><a href="/pay" class="hover:text-gold">Pay Fees Online</a></li>
        <li><a href="/timetable" class="hover:text-gold">Timetable</a></li>
        <li><a href="/news" class="hover:text-gold">News &amp; Events</a></li>
        <li><a href="/library-catalog" class="hover:text-gold">Library</a></li>
        <li><a href="/report" class="hover:text-gold">Anonymous Report</a></li>
        <li><a href="/parent/login" class="hover:text-gold">Parent Portal</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-bold mb-3 text-gold">Contact</h4>
      <ul class="space-y-2 text-sm text-white/80">
        <li>📍 Bungoma, Kenya</li>
        <li>📞 +254 700 000 000</li>
        <li>✉️ info@marell.ac.ke</li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10 py-4 text-center text-xs text-white/60">
    © {{ date('Y') }} Marell Academy. All rights reserved. Powered by Jenga Web School OS.
  </div>
</footer>

{{-- FLOATING WHATSAPP --}}
<a href="https://wa.me/254700000000" target="_blank" class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-green-500 flex items-center justify-center shadow-xl hover:scale-110 transition" aria-label="WhatsApp">
  <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
</a>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 700, once: true, easing: 'ease-out-cubic' });

  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  const overlay = document.getElementById('mobileOverlay');
  const closeBtn = document.getElementById('closeMenu');

  function openMenu(){ mobileMenu.classList.remove('translate-x-full'); overlay.classList.remove('hidden'); }
  function closeMenu(){ mobileMenu.classList.add('translate-x-full'); overlay.classList.add('hidden'); }

  hamburger.addEventListener('click', openMenu);
  overlay.addEventListener('click', closeMenu);
  closeBtn.addEventListener('click', closeMenu);
</script>

@push('scripts')
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/service-worker.js').catch(() => {});
  });
}
</script>
@endpush
@stack('scripts')

@include('partials.assistant')
</body>
</html>
