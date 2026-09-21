<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#0B3D91">

<title>@yield('title', 'Admin') — Marell Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { navy:'#0B3D91', gold:'#D4AF37', dark:'#0a1128', sidebar:'#051937' }, fontFamily: { poppins:['Poppins','sans-serif'], inter:['Inter','sans-serif'] } } } }
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  h1,h2,h3 { font-family: 'Poppins', sans-serif; }
  .sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; color: #cbd5e1; font-size: 0.875rem; font-weight: 500; transition: all 0.15s; }
  .sidebar-link:hover { background: rgba(212,175,55,0.1); color: #D4AF37; }
  .sidebar-link.active { background: rgba(212,175,55,0.15); color: #D4AF37; font-weight: 600; }
  .sidebar-group-title { padding: 0.5rem 1rem; font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: #64748b; font-weight: 700; }
</style>
@stack('head')
</head>
<body class="bg-gray-100 min-h-screen">

{{-- MOBILE OVERLAY --}}
<div id="sbOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

{{-- SIDEBAR --}}
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-sidebar text-white z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
  <div class="p-4 border-b border-white/10">
    <a href="/principal" class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-lg">M</div>
      <div>
        <div class="font-extrabold text-lg leading-none" style="font-family:Poppins">MARELL</div>
        <div class="text-[9px] tracking-[0.25em] text-gold mt-1">ADMIN OS</div>
      </div>
    </a>
  </div>

  <nav class="p-3">
    <div class="sidebar-group-title">Overview</div>
    <a href="/principal/predictions" class="sidebar-link {{ request()->is('principal/predictions*') ? 'active' : '' }}">
      <span>🤖</span> AI Predictions
    </a>
    <a href="/principal/live-collection" class="sidebar-link {{ request()->is('principal/live-collection*') ? 'active' : '' }}">
      <span>🎥</span> Live Collection
    </a>
    <a href="/principal" class="sidebar-link {{ request()->is('principal') ? 'active' : '' }}">
      <span>📊</span> Dashboard
    </a>

    <div class="sidebar-group-title mt-3">Academics</div>
    <a href="/principal/students" class="sidebar-link {{ request()->is('principal/students*') ? 'active' : '' }}">
      <span>🎓</span> Students
    </a>
    <a href="/principal/results" class="sidebar-link {{ request()->is('principal/results*') ? 'active' : '' }}">
      <span>📈</span> Results
    </a>
    <a href="/principal/teachers" class="sidebar-link {{ request()->is('principal/teachers*') ? 'active' : '' }}">
      <span>👨‍🏫</span> Teachers
    </a>

    <div class="sidebar-group-title mt-3">Finance</div>
    <a href="/principal/finance" class="sidebar-link {{ request()->is('principal/finance*') ? 'active' : '' }}">
      <span>💰</span> Payments
    </a>
    <a href="/principal/fines" class="sidebar-link {{ request()->is('principal/fines*') ? 'active' : '' }}">
      <span>⚠️</span> Late Fines
    </a>
    <a href="/principal/board-report" class="sidebar-link {{ request()->is('principal/board-report*') ? 'active' : '' }}">
      <span>📊</span> Board Report
    </a>
    <a href="/principal/expenses" class="sidebar-link {{ request()->is('principal/expenses*') ? 'active' : '' }}">
      <span>💸</span> Expenses
    </a>
    <a href="/principal/finance/defaulters" class="sidebar-link {{ request()->is('principal/finance/defaulters*') ? 'active' : '' }}">
      <span>⚠️</span> Defaulters
    </a>

    <div class="sidebar-group-title mt-3">Communication</div>
    <a href="/principal/voice-sms" class="sidebar-link {{ request()->is('principal/voice-sms*') ? 'active' : '' }}">
      <span>🎙️</span> Voice SMS
    </a>
    <a href="/principal/sms" class="sidebar-link {{ request()->is('principal/sms*') ? 'active' : '' }}">
      <span>📱</span> SMS Center
    </a>
    <a href="/principal/news" class="sidebar-link {{ request()->is('principal/news*') ? 'active' : '' }}">
      <span>📰</span> Website CMS
    </a>

    <div class="sidebar-group-title mt-3">System</div>
    <a href="/principal/audit" class="sidebar-link {{ request()->is('principal/audit*') ? 'active' : '' }}">
      <span>🔍</span> Audit Log
    </a>
    <a href="/principal/users" class="sidebar-link {{ request()->is('principal/users*') ? 'active' : '' }}">
      <span>👥</span> Users
    </a>
    <a href="/principal/confessions" class="sidebar-link {{ request()->is('principal/confessions*') ? 'active' : '' }}">
      <span>🔒</span> Anonymous Reports
    </a>
    <a href="/principal/contacts" class="sidebar-link {{ request()->is('principal/contacts*') ? 'active' : '' }}">
      <span>✉️</span> Messages
    </a>
    <a href="/principal/admissions" class="sidebar-link {{ request()->is('principal/admissions*') ? 'active' : '' }}">
      <span>📥</span> Applications
    </a>
  </nav>
</aside>

{{-- MAIN --}}
<div class="lg:ml-64">
  {{-- TOP BAR --}}
  <header class="sticky top-0 z-30 bg-white border-b shadow-sm">
    <div class="px-4 py-3 flex items-center gap-3">
      <button id="sbToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <div class="hidden md:flex items-center flex-1 max-w-md">
        <div class="relative w-full">
          <input id="masterSearch" type="text" placeholder="🔍 Search student, phone, receipt..."
                 class="w-full min-h-[42px] rounded-xl bg-gray-100 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gold">
          <div id="searchResults" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border max-h-96 overflow-y-auto hidden z-50"></div>
        </div>
      </div>

      <div class="ml-auto flex items-center gap-3">
      <div class="relative" id="quickJump">
        <button onclick="document.getElementById('qjMenu').classList.toggle('hidden')" class="text-xs text-navy font-semibold px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200">⚡ Quick Jump ▾</button>
        <div id="qjMenu" class="hidden absolute right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border py-2 min-w-[200px] z-50">
          <a href="/principal" class="block px-4 py-2 text-sm hover:bg-gray-50">👑 Principal</a>
          <a href="/dos" class="block px-4 py-2 text-sm hover:bg-gray-50">📚 DOS</a>
          <a href="/bursar" class="block px-4 py-2 text-sm hover:bg-gray-50">💰 Bursar</a>
          <a href="/teacher" class="block px-4 py-2 text-sm hover:bg-gray-50">👨‍🏫 Teacher</a>
          <div class="border-t my-1"></div>
          <a href="/" class="block px-4 py-2 text-sm hover:bg-gray-50 text-gray-500">🌐 Public Website</a>
          <a href="/logout" onclick="event.preventDefault(); document.getElementById('logoutForm')?.submit();" class="block px-4 py-2 text-sm hover:bg-gray-50 text-red-600">🚪 Logout</a>
        </div>
      </div>
      
        <div class="hidden sm:block text-right">
          <div class="text-xs text-gray-500">Signed in as</div>
          <div class="font-semibold text-navy text-sm">{{ auth()->user()->name ?? 'Guest' }}</div>
        </div>
        <div class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center font-bold">
          {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
        </div>
        <form method="POST" action="/logout">
          @csrf
          <a href="/password" class="text-xs text-gray-500 hover:text-gold px-2 py-1">Password</a><button class="text-xs text-gray-500 hover:text-red-600 px-2 py-1 rounded">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-6">
    @yield('content')
  </main>

  <footer class="px-6 py-4 text-center text-xs text-gray-400">
    Jenga Web School OS · v1.0 · Marell Academy {{ date('Y') }}
  </footer>
</div>

<script>
  const sbToggle  = document.getElementById('sbToggle');
  const sidebar   = document.getElementById('sidebar');
  const sbOverlay = document.getElementById('sbOverlay');

  function openSb(){ sidebar.classList.remove('-translate-x-full'); sbOverlay.classList.remove('hidden'); }
  function closeSb(){ sidebar.classList.add('-translate-x-full'); sbOverlay.classList.add('hidden'); }

  sbToggle?.addEventListener('click', openSb);
  sbOverlay?.addEventListener('click', closeSb);

  // Master search (basic version — full version in 4E)
  const ms = document.getElementById('masterSearch');
  const sr = document.getElementById('searchResults');
  if (ms && sr) {
    let timer;
    ms.addEventListener('input', () => {
      clearTimeout(timer);
      const q = ms.value.trim();
      if (q.length < 2) { sr.classList.add('hidden'); return; }
      timer = setTimeout(async () => {
        try {
          const r = await fetch('/principal/search?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }});
          const data = await r.json();
          if (!data.results?.length) { sr.innerHTML = '<div class="p-4 text-sm text-gray-500">No results</div>'; sr.classList.remove('hidden'); return; }
          sr.innerHTML = data.results.map(x => `
            <a href="${x.url}" class="block p-3 hover:bg-gray-50 border-b last:border-0">
              <div class="font-semibold text-navy text-sm">${x.title}</div>
              <div class="text-xs text-gray-500">${x.subtitle || ''}</div>
            </a>
          `).join('');
          sr.classList.remove('hidden');
        } catch (e) { sr.classList.add('hidden'); }
      }, 250);
    });
    document.addEventListener('click', (e) => {
      if (!ms.contains(e.target) && !sr.contains(e.target)) sr.classList.add('hidden');
    });
  }
</script>

@stack('scripts')
</body>
</html>
