<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LIVE — Today's Collection</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'}}}}</script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body{font-family:Inter,sans-serif;background:linear-gradient(135deg,#051937 0%,#0B3D91 100%);}
  h1,h2{font-family:Poppins,sans-serif;font-weight:800;}
  @keyframes slideIn { from{opacity:0;transform:translateX(-20px);} to{opacity:1;transform:translateX(0);} }
  .new-row { animation: slideIn .5s ease-out; }
  .pulse-ring { animation: pulse 2s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.5;} }
</style>
</head>
<body class="min-h-screen text-white">
<div class="p-6 md:p-10">

  <div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-2xl">M</div>
      <div>
        <div class="text-2xl font-extrabold">MARELL ACADEMY</div>
        <div class="text-xs tracking-widest text-gold font-bold">LIVE COLLECTION MONITOR</div>
      </div>
    </div>
    <div class="text-right">
      <div class="text-xs tracking-widest opacity-70">TODAY · {{ now()->format('l, d M Y') }}</div>
      <div class="flex items-center gap-2 mt-1 justify-end">
        <div class="w-2 h-2 rounded-full bg-green-400 pulse-ring"></div>
        <div class="text-xs font-bold text-green-400">LIVE</div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
    <div class="bg-white/5 backdrop-blur rounded-3xl p-8 border border-white/10">
      <div class="text-xs tracking-widest text-gold font-bold mb-3">TOTAL COLLECTED TODAY</div>
      <div id="totalAmount" class="text-5xl md:text-7xl font-extrabold">KES 0</div>
      <div class="text-sm opacity-60 mt-3">Auto-updating in real time</div>
    </div>
    <div class="bg-white/5 backdrop-blur rounded-3xl p-8 border border-white/10">
      <div class="text-xs tracking-widest text-gold font-bold mb-3">TRANSACTIONS</div>
      <div id="totalCount" class="text-5xl md:text-7xl font-extrabold">0</div>
      <div class="text-sm opacity-60 mt-3">Payments received</div>
    </div>
  </div>

  <div class="bg-white/5 backdrop-blur rounded-3xl p-6 border border-white/10">
    <div class="flex items-center gap-2 mb-6">
      <div class="w-2 h-2 rounded-full bg-red-500"></div>
      <h2 class="text-lg tracking-widest font-bold">LIVE PAYMENT FEED</h2>
    </div>
    <div id="feed" class="space-y-3 min-h-[400px]">
      <div class="text-center py-16 opacity-50">Waiting for payments...</div>
    </div>
  </div>

</div>

<script>
let sinceId = 0;
let currentTotal = 0;

async function refresh() {
  try {
    const r = await fetch('/principal/live-collection/feed?since_id=' + sinceId, { headers: {'Accept': 'application/json'} });
    const d = await r.json();

    animateNumber('totalAmount', currentTotal, d.today_total, 'KES ');
    const cntEl = document.getElementById('totalCount');
    animateNumber('totalCount', parseInt((cntEl.textContent || '0').replace(/\D/g, '')) || 0, d.today_count, '');
    currentTotal = d.today_total;

    if (d.new_payments && d.new_payments.length) {
      d.new_payments.forEach(p => { triggerConfetti(); prependFeed(p); });
      sinceId = d.latest_id;
    } else if (document.getElementById('feed').children.length === 0 && d.recent && d.recent.length) {
      document.getElementById('feed').innerHTML = '';
      d.recent.forEach(p => prependFeed(p, false));
    }
  } catch(e) { console.error(e); }
}

function prependFeed(p, animate = true) {
  const feed = document.getElementById('feed');
  const div = document.createElement('div');
  div.className = 'bg-white/10 rounded-2xl p-4 flex items-center justify-between border-l-4 border-green-400' + (animate ? ' new-row' : '');
  div.innerHTML = `
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-full bg-gold text-navy flex items-center justify-center font-bold text-lg">&#10003;</div>
      <div>
        <div class="font-bold text-lg">${p.student}</div>
        <div class="text-xs opacity-60">${p.adm || ''} &middot; ${p.class || ''} &middot; ${p.method}</div>
      </div>
    </div>
    <div class="text-right">
      <div class="text-2xl font-extrabold text-green-400">+ KES ${Number(p.amount).toLocaleString()}</div>
      <div class="text-xs opacity-60">${p.time}</div>
    </div>`;
  feed.insertBefore(div, feed.firstChild);
  while (feed.children.length > 15) feed.removeChild(feed.lastChild);
}

function animateNumber(id, from, to, prefix) {
  const el = document.getElementById(id);
  const start = performance.now();
  const duration = 800;
  function step(now) {
    const t = Math.min((now - start) / duration, 1);
    const val = from + (to - from) * (1 - Math.pow(1 - t, 3));
    el.textContent = prefix + Math.round(val).toLocaleString();
    if (t < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function triggerConfetti() {
  confetti({ particleCount: 120, spread: 90, origin: { y: 0.5 }, colors: ['#D4AF37', '#0B3D91', '#ffffff', '#22c55e'] });
}

refresh();
setInterval(refresh, 3000);
</script>
</body>
</html>
