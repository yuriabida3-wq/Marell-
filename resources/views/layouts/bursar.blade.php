<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Bursar') — Marell Finance</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { navy:'#0B3D91', gold:'#D4AF37', sidebar:'#051937' }, fontFamily: { poppins:['Poppins','sans-serif'], inter:['Inter','sans-serif'] } } } }
</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  h1,h2,h3 { font-family: 'Poppins', sans-serif; }
  .quick-btn { min-height: 56px; display: flex; align-items: center; justify-content: center; gap: .5rem; border-radius: 1rem; font-weight: 700; font-size: 14px; transition: all .15s; }
  .quick-btn:active { transform: scale(.97); }
  .sl { display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem; border-radius: .75rem; color: #cbd5e1; font-size: .875rem; font-weight: 500; }
  .sl:hover, .sl.active { background: rgba(212,175,55,.15); color: #D4AF37; }
</style>
@stack('head')
</head>
<body class="bg-gray-100 min-h-screen pb-24 md:pb-6">

<div id="sbOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-sidebar text-white z-50 -translate-x-full lg:translate-x-0 transition-transform overflow-y-auto">
  <div class="p-4 border-b border-white/10">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-lg">M</div>
      <div>
        <div class="font-extrabold text-lg leading-none">MARELL</div>
        <div class="text-[9px] tracking-[0.25em] text-gold mt-1">BURSAR · FINANCE</div>
      </div>
    </div>
  </div>

  <nav class="p-3">
    <a href="/bursar" class="sl {{ request()->is('bursar') ? 'active' : '' }}"><span>📊</span> Dashboard</a>
    <a href="/bursar/students" class="sl {{ request()->is('bursar/students*') ? 'active' : '' }}"><span>🔍</span> Find Student</a>
    <a href="/bursar/record" class="sl {{ request()->is('bursar/record*') ? 'active' : '' }}"><span>➕</span> Record Payment</a>
    <a href="/bursar/payments" class="sl {{ request()->is('bursar/payments*') ? 'active' : '' }}"><span>💳</span> All Payments</a>
    <a href="/bursar/balances" class="sl {{ request()->is('bursar/balances*') ? 'active' : '' }}"><span>📊</span> Fee Balances</a>
    <a href="/bursar/daily" class="sl {{ request()->is('bursar/daily*') ? 'active' : '' }}"><span>📅</span> Daily Report</a>
    <a href="/qr-scanner" class="sl {{ request()->is('qr-scanner*') ? 'active' : '' }}"><span>📷</span> QR Scanner</a>
    <a href="/principal/wallet" class="sl {{ request()->is('principal/wallet*') ? 'active' : '' }}"><span>💰</span> Canteen Wallet</a>
    <a href="/canteen" class="sl {{ request()->is('canteen*') ? 'active' : '' }}"><span>🍔</span> Canteen</a>
    <a href="/library" class="sl {{ request()->is('library*') ? 'active' : '' }}"><span>📚</span> Library</a>
    <a href="/visitors/gate" class="sl {{ request()->is('visitors*') ? 'active' : '' }}"><span>🚪</span> Gate</a>
    <a href="/bursar/bulk" class="sl {{ request()->is('bursar/bulk*') ? 'active' : '' }}"><span>📥</span> Bulk Cash Upload</a>
  </nav>
</aside>

<div class="lg:ml-64">
  <header class="sticky top-0 z-30 bg-white border-b shadow-sm">
    <div class="px-4 py-3 flex items-center gap-3">
      <button id="sbToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="font-bold text-navy flex-1">{{ auth()->user()->name }}</div>
      <div class="relative" id="quickJump">
        <button onclick="document.getElementById('qjMenu').classList.toggle('hidden')" class="text-xs text-navy font-semibold px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200">⚡ Jump ▾</button>
        <div id="qjMenu" class="hidden absolute right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border py-2 min-w-[200px] z-50">
          <a href="/principal" class="block px-4 py-2 text-sm hover:bg-gray-50">👑 Principal</a>
          <a href="/dos" class="block px-4 py-2 text-sm hover:bg-gray-50">📚 DOS</a>
          <a href="/bursar" class="block px-4 py-2 text-sm hover:bg-gray-50">💰 Bursar</a>
          <a href="/teacher" class="block px-4 py-2 text-sm hover:bg-gray-50">👨‍🏫 Teacher</a>
          <div class="border-t my-1"></div>
          <a href="/" class="block px-4 py-2 text-sm hover:bg-gray-50 text-gray-500">🌐 Website</a>
        </div>
      </div>
      <form method="POST" action="/logout">
        @csrf
        <a href="/password" class="text-xs text-gray-500 hover:text-gold px-2 py-1">Password</a><button class="text-xs text-gray-500 hover:text-red-600 px-2 py-1">Logout</button>
      </form>
    </div>
  </header>

  <main class="p-4 md:p-6">@yield('content')</main>
</div>

{{-- MOBILE STICKY BOTTOM --}}
<div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-2xl p-2 flex gap-2 lg:hidden z-30">
  <a href="/bursar/students" class="quick-btn flex-1 bg-navy text-white text-xs">🔍 Search</a>
  <a href="/bursar/record" class="quick-btn flex-1 bg-gold text-navy text-xs">➕ Record</a>
  <a href="/bursar/daily" class="quick-btn flex-1 bg-gray-100 text-navy text-xs">📅 Daily</a>
</div>

<script>
  document.getElementById('sbToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sbOverlay').classList.toggle('hidden');
  });
  document.getElementById('sbOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sbOverlay').classList.add('hidden');
  });
</script>
@stack('scripts')
<form id="logoutForm" method="POST" action="/logout" style="display:none;">@csrf</form>
</body>
</html>
