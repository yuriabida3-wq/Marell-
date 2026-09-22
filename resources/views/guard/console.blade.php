<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#051937">
<title>Gate Console — Marell</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37',dark:'#051937'},fontFamily:{poppins:['Poppins','sans-serif'],inter:['Inter','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { -webkit-tap-highlight-color: transparent; }
  body {
    font-family: 'Inter', sans-serif;
    background: #051937;
    color: #fff;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    overflow-x: hidden;
    user-select: none;
  }
  h1, h2, h3 { font-family: 'Poppins', sans-serif; font-weight: 800; }

  .stat-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 1rem;
    padding: 1rem;
    backdrop-filter: blur(10px);
    transition: all 0.2s;
  }
  .stat-card:active { transform: scale(0.97); }

  .action-btn {
    min-height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 1.25rem;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.15s;
    cursor: pointer;
    border: none;
    position: relative;
    overflow: hidden;
  }
  .action-btn:active { transform: scale(0.97); }
  .action-btn .icon { font-size: 2.2rem; line-height: 1; }
  .action-btn .label { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.03em; }

  .btn-scan { background: linear-gradient(135deg, #D4AF37, #b8932c); color: #051937; }
  .btn-visitor { background: linear-gradient(135deg, #0B3D91, #062a6b); color: #fff; border: 1px solid rgba(255,255,255,0.15); }
  .btn-list { background: rgba(255,255,255,0.08); color: #fff; border: 1px solid rgba(255,255,255,0.12); }
  .btn-panic { background: linear-gradient(135deg, #dc2626, #991b1b); color: #fff; }

  .btn-panic.armed {
    animation: pulse-red 1.5s infinite;
  }
  @keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.7); }
    50% { box-shadow: 0 0 0 15px rgba(220,38,38,0); }
  }

  .feed-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 0.75rem;
    background: rgba(255,255,255,0.04);
    align-items: center;
    animation: slideIn 0.4s ease-out;
  }
  @keyframes slideIn {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
  }

  .feed-icon {
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    flex-shrink: 0;
  }
  .feed-green { background: rgba(34,197,94,0.2); color: #4ade80; }
  .feed-red { background: rgba(220,38,38,0.2); color: #f87171; }
  .feed-blue { background: rgba(59,130,246,0.2); color: #60a5fa; }
  .feed-gray { background: rgba(148,163,184,0.2); color: #cbd5e1; }
  .feed-yellow { background: rgba(234,179,8,0.2); color: #fbbf24; }

  /* Modal */
  .modal-bg {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    z-index: 100;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(4px);
  }
  .modal-bg.active { display: flex; }
  .modal {
    background: #0a2145;
    border-radius: 1.5rem;
    width: 100%;
    max-width: 420px;
    max-height: 90vh;
    overflow-y: auto;
    animation: popIn 0.25s ease-out;
  }
  @keyframes popIn {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
  }
  .modal input, .modal textarea {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    color: #fff;
    border-radius: 0.75rem;
    padding: 0.85rem 1rem;
    width: 100%;
    font-size: 1rem;
  }
  .modal input:focus, .modal textarea:focus { outline: none; border-color: #D4AF37; }

  /* Toast */
  .toast {
    position: fixed;
    top: 1rem;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    background: #0B3D91;
    color: #fff;
    padding: 1rem 1.5rem;
    border-radius: 1rem;
    font-weight: 600;
    z-index: 200;
    transition: transform 0.3s ease-out;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    max-width: 90vw;
  }
  .toast.show { transform: translateX(-50%) translateY(0); }
  .toast.green { background: #16a34a; }
  .toast.red { background: #dc2626; }
  .toast.gold { background: #D4AF37; color: #051937; }

  /* Camera fullscreen */
  .cam-full {
    position: fixed;
    inset: 0;
    background: #000;
    z-index: 150;
    display: none;
    flex-direction: column;
  }
  .cam-full.active { display: flex; }
  #reader { flex: 1; overflow: hidden; }
  #reader video { object-fit: cover !important; }

  .live-dot {
    width: 8px; height: 8px; border-radius: 9999px; background: #4ade80;
    animation: pulse 2s infinite;
  }
  @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.3;} }
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="sticky top-0 z-30 backdrop-blur-lg" style="background: rgba(5,25,55,0.9); border-bottom: 1px solid rgba(255,255,255,0.08);">
  <div class="px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-gold text-navy flex items-center justify-center font-bold">M</div>
      <div>
        <div class="font-bold text-sm">{{ session('guard_name') ?? 'Guard' }}</div>
        <div class="text-[10px] tracking-widest text-gold font-bold">GATE CONSOLE</div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <div class="flex items-center gap-1.5">
        <div class="live-dot"></div>
        <span class="text-[10px] font-bold text-green-400">LIVE</span>
      </div>
      <span id="clock" class="text-xs font-mono text-gray-400">--:--:--</span>
      <form method="POST" action="/logout" id="logoutForm" class="inline">
        @csrf
        <button class="text-xs text-gray-500 px-2 py-1">Exit</button>
      </form>
    </div>
  </div>
</div>

<!-- STATS -->
<div class="px-4 py-4 grid grid-cols-2 gap-3">
  <div class="stat-card">
    <div class="text-[10px] tracking-widest text-gray-400 font-bold">INSIDE NOW</div>
    <div class="text-3xl font-extrabold mt-1" id="stat-inside">0</div>
  </div>
  <div class="stat-card">
    <div class="text-[10px] tracking-widest text-gray-400 font-bold">TODAY IN</div>
    <div class="text-3xl font-extrabold mt-1" id="stat-today-in">0</div>
  </div>
  <div class="stat-card">
    <div class="text-[10px] tracking-widest text-gray-400 font-bold">RELEASES</div>
    <div class="text-3xl font-extrabold mt-1 text-green-400" id="stat-pickups">0</div>
  </div>
  <div class="stat-card">
    <div class="text-[10px] tracking-widest text-gray-400 font-bold">DENIED</div>
    <div class="text-3xl font-extrabold mt-1 text-red-400" id="stat-denied">0</div>
  </div>
</div>

<!-- MAIN ACTIONS -->
<div class="px-4 pb-4">
  <div class="grid grid-cols-2 gap-3 mb-3">
    <button onclick="openScanner()" class="action-btn btn-scan">
      <div class="icon">📷</div>
      <div class="label">SCAN PICKUP</div>
    </button>
    <button onclick="openVisitorModal()" class="action-btn btn-visitor">
      <div class="icon">🚪</div>
      <div class="label">CHECK IN VISITOR</div>
    </button>
    <button onclick="openInsideModal()" class="action-btn btn-list">
      <div class="icon">👥</div>
      <div class="label">WHO'S INSIDE</div>
    </button>
    <button onclick="openQuickPickup()" class="action-btn btn-list">
      <div class="icon">🎯</div>
      <div class="label">QUICK VERIFY</div>
    </button>
  </div>

  <!-- PANIC -->
  <button id="panicBtn" onclick="triggerPanic()" class="action-btn btn-panic w-full" style="min-height: 90px;">
    <div class="icon">🚨</div>
    <div class="label">PANIC ALERT · DIRECTOR + STAFF</div>
  </button>
</div>

<!-- LIVE FEED -->
<div class="px-4 pb-8">
  <div class="flex items-center justify-between mb-3">
    <div class="text-xs font-bold tracking-widest text-gray-400">LIVE ACTIVITY</div>
    <div class="text-[10px] text-gray-500">Auto-refresh</div>
  </div>
  <div id="feed" class="space-y-2">
    <div class="text-center text-gray-600 text-xs py-8">Loading activity...</div>
  </div>
</div>

<!-- TOAST -->
<div id="toast" class="toast"></div>

<!-- ============ MODAL: VISITOR CHECK IN ============ -->
<div id="visitorModal" class="modal-bg">
  <div class="modal p-6">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-xl">🚪 Check In Visitor</h3>
      <button onclick="closeModal('visitorModal')" class="text-gray-400 text-2xl leading-none">&times;</button>
    </div>
    <form onsubmit="submitVisitor(event)" class="space-y-3">
      <div>
        <label class="block text-[10px] tracking-widest font-bold text-gold mb-2">VISITOR NAME *</label>
        <input name="name" required autofocus placeholder="Full name">
      </div>
      <div>
        <label class="block text-[10px] tracking-widest font-bold text-gold mb-2">PHONE *</label>
        <input name="phone" required placeholder="07XXXXXXXX">
      </div>
      <div>
        <label class="block text-[10px] tracking-widest font-bold text-gold mb-2">PURPOSE *</label>
        <input name="purpose" required placeholder="e.g. Meet Principal">
      </div>
      <button class="w-full py-4 rounded-xl bg-gold text-navy font-bold">✅ Check In & Issue Badge</button>
    </form>
  </div>
</div>

<!-- ============ MODAL: WHO'S INSIDE ============ -->
<div id="insideModal" class="modal-bg">
  <div class="modal p-6">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-xl">👥 Who's Inside</h3>
      <button onclick="closeModal('insideModal')" class="text-gray-400 text-2xl leading-none">&times;</button>
    </div>
    <div id="insideList" class="space-y-2 max-h-[70vh] overflow-y-auto">
      <div class="text-center text-gray-500 py-4 text-sm">Loading...</div>
    </div>
  </div>
</div>

<!-- ============ MODAL: QUICK PICKUP VERIFY ============ -->
<div id="quickPickupModal" class="modal-bg">
  <div class="modal p-6">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-xl">🎯 Quick Verify</h3>
      <button onclick="closeModal('quickPickupModal')" class="text-gray-400 text-2xl leading-none">&times;</button>
    </div>
    <div class="text-sm text-gray-400 mb-4">Pick an approved pickup to verify (no camera needed):</div>
    <select id="pickupDropdown" class="w-full mb-3 rounded-xl px-4 py-3 text-base" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: #fff;">
      <option value="">Loading...</option>
    </select>
    <button onclick="verifyPickup()" class="w-full py-4 rounded-xl bg-gold text-navy font-bold mb-2">✅ Verify</button>
    <button onclick="verifyFake()" class="w-full py-3 rounded-xl text-red-400 font-semibold text-sm" style="background: rgba(220,38,38,0.15);">⛔ Test Invalid Token</button>
  </div>
</div>

<!-- ============ FULLSCREEN CAMERA ============ -->
<div id="camFull" class="cam-full">
  <div class="px-4 py-3 flex items-center justify-between" style="background: rgba(5,25,55,0.95);">
    <button onclick="closeScanner()" class="text-white text-sm">← Back</button>
    <div class="font-bold text-sm">📷 Scan Pickup QR</div>
    <div class="w-12"></div>
  </div>
  <div id="reader"></div>
  <div class="p-4" style="background: rgba(5,25,55,0.95);">
    <div id="camStatus" class="text-center text-sm text-gray-400">Tap "Start Camera" below</div>
    <div class="flex gap-2 mt-3">
      <button id="camStartBtn" onclick="startCamera()" class="flex-1 py-4 rounded-xl bg-gold text-navy font-bold">▶ Start Camera</button>
      <button id="camStopBtn" onclick="stopCamera()" class="flex-1 py-4 rounded-xl text-white font-bold hidden" style="background: rgba(220,38,38,0.8);">⏹ Stop</button>
    </div>
  </div>
</div>

<!-- ============ RESULT FULLSCREEN ============ -->
<div id="resultScreen" class="modal-bg" style="z-index: 180;">
  <div id="resultContent" class="w-full max-w-md"></div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ============ STATE ============
let scanner = null;
let scanning = false;
let toastTimer = null;
const CSRF = '{{ csrf_token() }}';

// ============ CLOCK ============
function tickClock() {
  const d = new Date();
  document.getElementById('clock').textContent =
    String(d.getHours()).padStart(2,'0') + ':' +
    String(d.getMinutes()).padStart(2,'0') + ':' +
    String(d.getSeconds()).padStart(2,'0');
}
setInterval(tickClock, 1000);
tickClock();

// ============ TOAST ============
function toast(msg, type = 'gold') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ============ MODALS ============
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function openVisitorModal() {
  openModal('visitorModal');
  setTimeout(() => document.querySelector('#visitorModal input[name=name]').focus(), 200);
}

async function openInsideModal() {
  openModal('insideModal');
  const box = document.getElementById('insideList');
  box.innerHTML = '<div class="text-center text-gray-500 py-4 text-sm">Loading...</div>';
  try {
    const r = await fetch('/gate/console/inside', { headers: {'Accept':'application/json'} });
    const d = await r.json();
    if (!d.inside || !d.inside.length) {
      box.innerHTML = '<div class="text-center text-gray-500 py-6 text-sm">No visitors inside 🕊️</div>';
      return;
    }
    box.innerHTML = d.inside.map(v => `
      <div class="flex items-center justify-between p-3 rounded-xl" style="background: rgba(255,255,255,0.05);">
        <div>
          <div class="font-bold text-sm">${v.name}</div>
          <div class="text-xs text-gray-400">${v.purpose} · Badge ${v.badge || '—'} · Since ${v.since}</div>
        </div>
        <button onclick="checkOutVisitor(${v.id})" class="px-3 py-2 rounded-lg text-xs font-bold" style="background: rgba(220,38,38,0.3); color: #f87171;">OUT</button>
      </div>`).join('');
  } catch(e) {
    box.innerHTML = '<div class="text-center text-red-400 py-4 text-sm">Failed to load</div>';
  }
}

async function openQuickPickup() {
  openModal('quickPickupModal');
  const sel = document.getElementById('pickupDropdown');
  sel.innerHTML = '<option>Loading...</option>';
  try {
    const r = await fetch('/security/pickups-list', { headers: {'Accept':'application/json'} });
    const d = await r.json();
    if (!d.pickups || !d.pickups.length) {
      sel.innerHTML = '<option value="">No approved pickups</option>';
      return;
    }
    sel.innerHTML = '<option value="">-- Select pickup --</option>' +
      d.pickups.map(p => `<option value="${p.token}">${p.name} → ${p.student}</option>`).join('');
  } catch(e) {
    sel.innerHTML = '<option value="">Failed to load</option>';
  }
}

// ============ VISITOR CHECK IN ============
async function submitVisitor(e) {
  e.preventDefault();
  const form = e.target;
  const data = {
    name: form.name.value,
    phone: form.phone.value,
    purpose: form.purpose.value,
  };
  try {
    const r = await fetch('/gate/console/visitor', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': CSRF},
      body: JSON.stringify(data),
    });
    const d = await r.json();
    if (d.status === 'ok') {
      closeModal('visitorModal');
      form.reset();
      toast('✅ ' + d.message, 'green');
      refreshStats();
      refreshFeed();
    } else {
      toast('❌ ' + (d.message || 'Error'), 'red');
    }
  } catch(err) {
    toast('❌ Network error', 'red');
  }
}

async function checkOutVisitor(id) {
  if (!confirm('Check out this visitor?')) return;
  try {
    const r = await fetch('/gate/console/visitor-out', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': CSRF},
      body: JSON.stringify({ visitor_id: id }),
    });
    const d = await r.json();
    if (d.status === 'ok') {
      toast('✅ ' + d.message, 'green');
      openInsideModal();
      refreshStats();
      refreshFeed();
    }
  } catch(e) { toast('❌ Network error', 'red'); }
}

// ============ PICKUP VERIFY ============
async function verifyPickup() {
  const tok = document.getElementById('pickupDropdown').value;
  if (!tok) { toast('Pick a pickup first', 'red'); return; }
  closeModal('quickPickupModal');
  await verifyToken(tok);
}

async function verifyFake() {
  closeModal('quickPickupModal');
  await verifyToken('INVALID-FAKE-' + Date.now());
}

async function verifyToken(token) {
  try {
    const r = await fetch('{{ route("security.verify") }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': CSRF},
      body: JSON.stringify({ token }),
    });
    const d = await r.json();
    showResult(d);
    refreshStats();
    refreshFeed();
  } catch(e) {
    toast('❌ Network error', 'red');
  }
}

function showResult(d) {
  const box = document.getElementById('resultContent');
  const modal = document.getElementById('resultScreen');

  let html = '';
  if (d.status === 'verified') {
    if (navigator.vibrate) navigator.vibrate(200);
    html = `
      <div style="background: linear-gradient(135deg,#16a34a,#15803d); border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 20px 60px rgba(22,163,74,0.4);">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
          <div style="width: 72px; height: 72px; border-radius: 9999px; background: #fff; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800;">✓</div>
          <div>
            <div style="font-size: 2rem; font-weight: 800;">RELEASE</div>
            <div style="opacity: 0.9; font-size: 0.9rem;">Approved pickup</div>
          </div>
        </div>
        <div style="background: rgba(255,255,255,0.2); border-radius: 1rem; padding: 1rem; font-size: 0.9rem;">
          <div style="margin-bottom: 0.5rem;"><b>Student:</b> ${d.student_name} (${d.student_adm})</div>
          <div style="margin-bottom: 0.5rem;"><b>Class:</b> ${d.student_class}</div>
          <div style="border-top: 1px solid rgba(255,255,255,0.3); padding-top: 0.5rem; margin-top: 0.5rem;"><b>Picker:</b> ${d.picker_name}</div>
          <div><b>Relationship:</b> ${d.relationship}</div>
          <div><b>Phone:</b> ${d.picker_phone}</div>
        </div>
      </div>`;
  } else if (d.status === 'denied' || d.status === 'invalid') {
    if (navigator.vibrate) navigator.vibrate([300,100,300]);
    html = `
      <div style="background: linear-gradient(135deg,#dc2626,#991b1b); border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 20px 60px rgba(220,38,38,0.4);">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <div style="width: 72px; height: 72px; border-radius: 9999px; background: #fff; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800;">✕</div>
          <div>
            <div style="font-size: 1.75rem; font-weight: 800;">DO NOT RELEASE</div>
            <div style="opacity: 0.9; font-size: 0.85rem; margin-top: 0.25rem;">${d.message || 'Invalid or unapproved pickup'}</div>
          </div>
        </div>
      </div>`;
  } else {
    html = `<div style="background: #f59e0b; border-radius: 1.5rem; padding: 1.5rem; text-align: center;">
      <div style="font-size: 3rem;">⚠️</div>
      <div style="font-size: 1.5rem; font-weight: 800; margin-top: 0.5rem;">PENDING</div>
    </div>`;
  }

  box.innerHTML = html + `<button onclick="closeResult()" style="display: block; width: 100%; margin-top: 1rem; padding: 1rem; border-radius: 1rem; background: rgba(255,255,255,0.15); color: #fff; font-weight: 700; border: none; font-size: 1rem;">Dismiss</button>`;
  modal.classList.add('active');
}

function closeResult() {
  document.getElementById('resultScreen').classList.remove('active');
}

// ============ CAMERA ============
function openScanner() {
  document.getElementById('camFull').classList.add('active');
}

function closeScanner() {
  document.getElementById('camFull').classList.remove('active');
  stopCamera();
}

async function startCamera() {
  if (scanning) return;
  const status = document.getElementById('camStatus');
  status.textContent = '🔄 Requesting camera...';

  scanner = new Html5Qrcode("reader");
  try {
    let cameraArg = { facingMode: "environment" };
    try {
      const cams = await Html5Qrcode.getCameras();
      if (cams && cams.length) {
        const back = cams.find(c => /back|rear|environment/i.test(c.label)) || cams[cams.length - 1];
        cameraArg = back.id;
      }
    } catch(e) {}

    await scanner.start(
      cameraArg,
      { fps: 10, qrbox: { width: 250, height: 250 } },
      (decoded) => {
        if (navigator.vibrate) navigator.vibrate(80);
        closeScanner();
        verifyToken(decoded);
      },
      () => {}
    );
    scanning = true;
    document.getElementById('camStartBtn').classList.add('hidden');
    document.getElementById('camStopBtn').classList.remove('hidden');
    status.textContent = '✅ Point camera at QR code';
  } catch(err) {
    status.textContent = '❌ ' + err.message;
    toast('Camera denied. Use Quick Verify instead.', 'red');
  }
}

async function stopCamera() {
  if (scanner && scanning) {
    try { await scanner.stop(); } catch(e) {}
    scanning = false;
  }
  document.getElementById('camStartBtn').classList.remove('hidden');
  document.getElementById('camStopBtn').classList.add('hidden');
}

// ============ PANIC ============
async function triggerPanic() {
  if (!confirm('🚨 SEND PANIC ALERT to Director + all staff?')) return;

  const btn = document.getElementById('panicBtn');
  btn.classList.add('armed');
  btn.querySelector('.label').textContent = 'SENDING ALERT...';

  try {
    const r = await fetch('{{ route("security.panic") }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': CSRF},
      body: JSON.stringify({ location: 'Main Gate' }),
    });
    const d = await r.json();
    if (navigator.vibrate) navigator.vibrate([300,100,300,100,300]);
    toast('🚨 ALERT SENT — ' + (d.message || 'Director notified'), 'red');
  } catch(e) {
    toast('❌ Failed to send. Call Director directly.', 'red');
  } finally {
    btn.classList.remove('armed');
    btn.querySelector('.label').textContent = 'PANIC ALERT · DIRECTOR + STAFF';
  }
}

// ============ POLLING ============
async function refreshStats() {
  try {
    const r = await fetch('/gate/console/stats', { headers: {'Accept':'application/json'} });
    const d = await r.json();
    if (d.error) return;
    document.getElementById('stat-inside').textContent = d.inside;
    document.getElementById('stat-today-in').textContent = d.today_in;
    document.getElementById('stat-pickups').textContent = d.pickups;
    document.getElementById('stat-denied').textContent = d.denied;
  } catch(e) {}
}

async function refreshFeed() {
  try {
    const r = await fetch('/gate/console/recent', { headers: {'Accept':'application/json'} });
    const d = await r.json();
    const box = document.getElementById('feed');
    if (!d.feed || !d.feed.length) {
      box.innerHTML = '<div class="text-center text-gray-600 text-xs py-8">No activity yet</div>';
      return;
    }
    box.innerHTML = d.feed.map(x => `
      <div class="feed-item">
        <div class="feed-icon feed-${x.color}">${x.icon}</div>
        <div style="flex: 1; min-width: 0;">
          <div class="font-bold text-sm">${x.title}</div>
          <div class="text-xs text-gray-400 truncate">${x.detail}</div>
        </div>
        <div class="text-xs text-gray-500">${x.at}</div>
      </div>`).join('');
  } catch(e) {}
}

refreshStats();
refreshFeed();
setInterval(refreshStats, 5000);
setInterval(refreshFeed, 5000);
</script>

</body>
</html>
