<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'DOS') — Marell Academic</title>
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

<div id="sbOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-sidebar text-white z-50 -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
  <div class="p-4 border-b border-white/10">
    <a href="/dos" class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-lg">M</div>
      <div>
        <div class="font-extrabold text-lg leading-none" style="font-family:Poppins">MARELL</div>
        <div class="text-[9px] tracking-[0.25em] text-gold mt-1">DOS · ACADEMIC</div>
      </div>
    </a>
  </div>

  <nav class="p-3">
    <div class="sidebar-group-title">Overview</div>
    <a href="/dos" class="sidebar-link {{ request()->is('dos') ? 'active' : '' }}"><span>📊</span> Dashboard</a>

    <div class="sidebar-group-title mt-3">Academics</div>
    <a href="/dos/classes" class="sidebar-link {{ request()->is('dos/classes*') ? 'active' : '' }}"><span>🏫</span> Classes</a>
    <a href="/dos/timetable" class="sidebar-link {{ request()->is('dos/timetable*') ? 'active' : '' }}"><span>📅</span> Timetable</a>
    <a href="/dos/exams" class="sidebar-link {{ request()->is('dos/exams*') ? 'active' : '' }}"><span>📝</span> Exams</a>
    <a href="/dos/marks" class="sidebar-link {{ request()->is('dos/marks*') ? 'active' : '' }}"><span>✍️</span> Marks Entry</a>

    <div class="sidebar-group-title mt-3">Students</div>
    <a href="/dos/students" class="sidebar-link {{ request()->is('dos/students*') ? 'active' : '' }}"><span>🎓</span> Register Student</a>
    <a href="/dos/promote" class="sidebar-link {{ request()->is('dos/promote*') ? 'active' : '' }}"><span>⬆️</span> Promote</a>

    <div class="sidebar-group-title mt-3">Teachers</div>
    <a href="/dos/teachers" class="sidebar-link {{ request()->is('dos/teachers*') ? 'active' : '' }}"><span>👨‍🏫</span> Manage Teachers</a>

    <div class="sidebar-group-title mt-3">Reports</div>
    <a href="/dos/report-cards" class="sidebar-link {{ request()->is('dos/report-cards*') ? 'active' : '' }}"><span>📄</span> Report Cards</a>
  </nav>
</aside>

<div class="lg:ml-64">
  <header class="sticky top-0 z-30 bg-white border-b shadow-sm">
    <div class="px-4 py-3 flex items-center gap-3">
      <button id="sbToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <div class="hidden md:flex items-center flex-1 max-w-md">
        <input id="dosSearch" type="text" placeholder="🔍 Search student..."
               class="w-full min-h-[42px] rounded-xl bg-gray-100 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gold">
      </div>

      <div class="ml-auto flex items-center gap-3">
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
    Jenga Web School OS · DOS · Marell Academy {{ date('Y') }}
  </footer>
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
</body>
</html>
