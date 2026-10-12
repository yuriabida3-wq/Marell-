<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin')</title>
@include('partials.theme')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
      <x-brand-logo size="md" />
    </a>
  </div>

  <nav class="p-3">
    <div class="sidebar-group-title">Overview</div>
    <a href="/principal/predictions" class="sidebar-link {{ request()->is('principal/predictions*') ? 'active' : '' }}">
      <x-icon name="cpu-chip" size="sm" /> AI Predictions
    </a>
    <a href="/principal/live-collection" class="sidebar-link {{ request()->is('principal/live-collection*') ? 'active' : '' }}">
      <x-icon name="video-camera" size="sm" /> Live Collection
    </a>
    <a href="/principal" class="sidebar-link {{ request()->is('principal') ? 'active' : '' }}">
      <x-icon name="squares-2x2" size="sm" /> Dashboard
    </a>

    <div class="sidebar-group-title mt-3">Academics</div>
    <a href="/principal/attendance" class="sidebar-link {{ request()->is('principal/attendance*') ? 'active' : '' }}">
      <x-icon name="check-circle" size="sm" /> Attendance
    </a>
    <a href="/principal/students" class="sidebar-link {{ request()->is('principal/students*') ? 'active' : '' }}">
      <x-icon name="academic-cap" size="sm" /> Students
    </a>
    <a href="/principal/results" class="sidebar-link {{ request()->is('principal/results*') ? 'active' : '' }}">
      <x-icon name="arrow-trending-up" size="sm" /> Results
    </a>
    <a href="/principal/teachers" class="sidebar-link {{ request()->is('principal/teachers*') ? 'active' : '' }}">
      <x-icon name="user-circle" size="sm" /> Teachers
    </a>

    <div class="sidebar-group-title mt-3">Finance</div>
    <a href="/principal/fee-autopilot" class="sidebar-link {{ request()->is('principal/fee-autopilot*') ? 'active' : '' }}">
      <x-icon name="cog-6-tooth" size="sm" /> Fee Autopilot
    </a>
    <a href="/principal/finance" class="sidebar-link {{ request()->is('principal/finance*') ? 'active' : '' }}">
      <x-icon name="banknotes" size="sm" /> Payments
    </a>
    <a href="/principal/fines" class="sidebar-link {{ request()->is('principal/fines*') ? 'active' : '' }}">
      <x-icon name="exclamation-triangle" size="sm" /> Late Fines
    </a>
    <a href="/principal/board-report" class="sidebar-link {{ request()->is('principal/board-report*') ? 'active' : '' }}">
      <x-icon name="document-text" size="sm" /> Board Report
    </a>
    <a href="/visitors/gate" class="sidebar-link {{ request()->is('visitors*') ? 'active' : '' }}">
      <x-icon name="door-open" size="sm" /> Gate
    </a>
    <a href="/library" class="sidebar-link {{ request()->is('library*') ? 'active' : '' }}">
      <x-icon name="book-open" size="sm" /> Library
    </a>
    <a href="/principal/wallet" class="sidebar-link {{ request()->is('principal/wallet*') ? 'active' : '' }}">
      <x-icon name="wallet" size="sm" /> Canteen Wallet
    </a>
    <a href="/canteen" class="sidebar-link {{ request()->is('canteen*') ? 'active' : '' }}">
      <x-icon name="shopping-cart" size="sm" /> Canteen
    </a>
    <a href="/principal/expenses" class="sidebar-link {{ request()->is('principal/expenses*') ? 'active' : '' }}">
      <x-icon name="arrow-trending-down" size="sm" /> Expenses
    </a>
    <a href="/principal/finance/defaulters" class="sidebar-link {{ request()->is('principal/finance/defaulters*') ? 'active' : '' }}">
      <x-icon name="exclamation-triangle" size="sm" /> Defaulters
    </a>

    <div class="sidebar-group-title mt-3">Communication</div>
    <a href="/principal/voice-sms" class="sidebar-link {{ request()->is('principal/voice-sms*') ? 'active' : '' }}">
      <x-icon name="microphone" size="sm" /> Voice SMS
    </a>
    <a href="/principal/emergency" class="sidebar-link {{ request()->is('principal/emergency*') ? 'active' : '' }}">
      <x-icon name="bell-alert" size="sm" /> Emergency Alert
    </a>
    <a href="/principal/sms" class="sidebar-link {{ request()->is('principal/sms*') ? 'active' : '' }}">
      <x-icon name="device-phone" size="sm" /> SMS Center
    </a>
    <a href="/principal/news" class="sidebar-link {{ request()->is('principal/news*') ? 'active' : '' }}">
      <x-icon name="newspaper" size="sm" /> Website CMS
    </a>

    <div class="sidebar-group-title mt-3">System</div>
    <a href="/qr-scanner" class="sidebar-link {{ request()->is('qr-scanner*') ? 'active' : '' }}">
      <x-icon name="camera" size="sm" /> QR Scanner
    </a>
    <a href="/principal/audit" class="sidebar-link {{ request()->is('principal/audit*') ? 'active' : '' }}">
      <x-icon name="document-magnifying-glass" size="sm" /> Audit Log
    </a>
    <a href="/principal/users" class="sidebar-link {{ request()->is('principal/users*') ? 'active' : '' }}">
      <x-icon name="users" size="sm" /> Users
    </a>
    <a href="/principal/confessions" class="sidebar-link {{ request()->is('principal/confessions*') ? 'active' : '' }}">
      <x-icon name="lock-closed" size="sm" /> Anonymous Reports
    </a>
    <a href="/principal/contacts" class="sidebar-link {{ request()->is('principal/contacts*') ? 'active' : '' }}">
      <x-icon name="envelope" size="sm" /> Messages
    </a>
    <a href="/principal/admissions" class="sidebar-link {{ request()->is('principal/admissions*') ? 'active' : '' }}">
      <x-icon name="inbox" size="sm" /> Applications
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
          <input id="masterSearch" type="text" placeholder="Search student, phone, receipt..."
                 class="w-full min-h-[42px] rounded-xl bg-gray-100 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gold">
          <div id="searchResults" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border max-h-96 overflow-y-auto hidden z-50"></div>
        </div>
      </div>

      <div class="ml-auto flex items-center gap-3">
      <div class="relative" id="quickJump">
        <button onclick="document.getElementById('qjMenu').classList.toggle('hidden')" class="text-xs text-navy font-semibold px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200"><x-icon name="bolt" size="sm" class="mr-1" />Quick Jump ▾</button>
        <div id="qjMenu" class="hidden absolute right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border py-2 min-w-[200px] z-50">
          <a href="/principal" class="block px-4 py-2 text-sm hover:bg-gray-50"><x-icon name="crown" size="sm" class="mr-1" />Principal</a>
          <a href="/dos" class="block px-4 py-2 text-sm hover:bg-gray-50"><x-icon name="academic-cap" size="sm" class="mr-1" />DOS</a>
          <a href="/bursar" class="block px-4 py-2 text-sm hover:bg-gray-50"><x-icon name="banknotes" size="sm" class="mr-1" />Bursar</a>
          <a href="/teacher" class="block px-4 py-2 text-sm hover:bg-gray-50"><x-icon name="user-circle" size="sm" class="mr-1" />Teacher</a>
          <div class="border-t my-1"></div>
          <a href="/" class="block px-4 py-2 text-sm hover:bg-gray-50 text-gray-500"><x-icon name="globe-alt" size="sm" class="mr-1" />Public Website</a>
          <a href="/logout" onclick="event.preventDefault(); document.getElementById('logoutForm')?.submit();" class="block px-4 py-2 text-sm hover:bg-gray-50 text-red-600"><x-icon name="arrow-right-on-rectangle" size="sm" class="mr-1" />Logout</a>
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
    {{ setting('school_name') }} · Admin · {{ date('Y') }} · {{ config('skuli.product.footer_text') }}
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
