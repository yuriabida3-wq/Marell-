<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scan Pickup QR</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif}h1{font-family:Poppins,sans-serif;font-weight:800}</style>
</head>
<body class="min-h-screen bg-gray-100 pb-10">

<div class="bg-navy text-white p-4">
  <div class="flex items-center justify-between max-w-2xl mx-auto">
    <a href="{{ route('security.dashboard') }}" class="text-sm">← Back</a>
    <div class="font-bold">📷 Scan Pickup QR</div>
    <div class="w-12"></div>
  </div>
</div>

<div class="max-w-2xl mx-auto p-4">

  <div id="resultBox" class="hidden mb-4"></div>

  <div id="reader" class="rounded-2xl overflow-hidden bg-black mb-4" style="min-height:320px;"></div>

  <div class="flex gap-2 mb-4">
    <button id="startBtn" class="flex-1 min-h-[52px] rounded-xl bg-gold text-navy font-bold">▶ Start Camera</button>
    <button id="stopBtn" class="flex-1 min-h-[52px] rounded-xl bg-red-600 text-white font-bold hidden">⏹ Stop</button>
  </div>

  <div class="bg-white rounded-2xl p-4 shadow">
    <div class="text-xs font-bold text-navy tracking-widest mb-2">OR TYPE TOKEN MANUALLY</div>
    <div class="flex gap-2">
      <input id="manualToken" placeholder="Paste QR token..." class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-3 text-sm">
      <button onclick="verifyToken(document.getElementById('manualToken').value)" class="px-4 rounded-xl bg-navy text-white text-sm font-semibold">Verify</button>
    </div>
  </div>

</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let scanner = null;
let running = false;
const reader = document.getElementById('reader');
const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const resultBox = document.getElementById('resultBox');

function showResult(html) {
  resultBox.classList.remove('hidden');
  resultBox.innerHTML = html;
  resultBox.scrollIntoView({ behavior: 'smooth' });
}

async function verifyToken(token) {
  if (!token || !token.trim()) return;
  showResult('<div class="bg-blue-50 p-4 rounded-lg"><div class="font-bold text-navy">⏳ Verifying...</div></div>');

  try {
    const r = await fetch('{{ route("security.verify") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ token: token.trim() })
    });
    const d = await r.json();
    render(d);
  } catch (e) {
    showResult('<div class="bg-red-50 p-4 rounded-lg"><div class="font-bold text-red-700">❌ Network error</div></div>');
  }
}

function render(d) {
  if (d.status === 'verified') {
    showResult(`
      <div class="bg-green-500 text-white rounded-2xl p-6 shadow-2xl">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-16 h-16 rounded-full bg-white text-green-600 flex items-center justify-center text-4xl font-bold">✓</div>
          <div>
            <div class="text-3xl font-extrabold">RELEASE</div>
            <div class="text-sm opacity-90">Approved pickup verified</div>
          </div>
        </div>
        <div class="bg-white/20 rounded-xl p-4 space-y-2 text-sm">
          <div><strong>Student:</strong> ${d.student_name} (${d.student_adm})</div>
          <div><strong>Class:</strong> ${d.student_class}</div>
          <div class="border-t border-white/30 pt-2"><strong>Picker:</strong> ${d.picker_name}</div>
          <div><strong>Relationship:</strong> ${d.relationship}</div>
          <div><strong>Phone:</strong> ${d.picker_phone}</div>
        </div>
      </div>
    `);
    if (navigator.vibrate) navigator.vibrate(200);
  } else if (d.status === 'denied') {
    showResult(`
      <div class="bg-red-600 text-white rounded-2xl p-6 shadow-2xl">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-full bg-white text-red-600 flex items-center justify-center text-4xl font-bold">✕</div>
          <div>
            <div class="text-3xl font-extrabold">DO NOT RELEASE</div>
            <div class="text-sm opacity-90">${d.message}</div>
          </div>
        </div>
      </div>
    `);
    if (navigator.vibrate) navigator.vibrate([300, 100, 300]);
  } else {
    showResult(`
      <div class="bg-red-600 text-white rounded-2xl p-6 shadow-2xl">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-full bg-white text-red-600 flex items-center justify-center text-4xl font-bold">!</div>
          <div>
            <div class="text-3xl font-extrabold">INVALID</div>
            <div class="text-sm opacity-90">${d.message}</div>
          </div>
        </div>
      </div>
    `);
    if (navigator.vibrate) navigator.vibrate([300, 100, 300, 100, 300]);
  }
}

startBtn.addEventListener('click', async () => {
  if (running) return;
  scanner = new Html5Qrcode("reader");
  try {
    await scanner.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: { width: 250, height: 250 } },
      (decoded) => { verifyToken(decoded); if (navigator.vibrate) navigator.vibrate(100); },
      () => {}
    );
    running = true;
    startBtn.classList.add('hidden');
    stopBtn.classList.remove('hidden');
  } catch (e) {
    alert('Camera failed. Use manual entry below. Camera needs HTTPS or localhost.');
  }
});

stopBtn.addEventListener('click', async () => {
  if (scanner && running) {
    await scanner.stop();
    running = false;
    startBtn.classList.remove('hidden');
    stopBtn.classList.add('hidden');
  }
});
</script>

</body>
</html>
