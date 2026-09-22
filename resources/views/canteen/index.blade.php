<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Canteen</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'}}}}</script>
<style>body{font-family:Inter,sans-serif;background:#f3f4f6}</style>
</head>
<body class="min-h-screen pb-10">

<div class="bg-navy text-white p-4">
  <div class="max-w-3xl mx-auto flex items-center justify-between">
    <a href="{{ session('guard_id') ? route('security.dashboard') : '/principal' }}" class="text-sm">← Back</a>
    <div class="font-bold">🍔 Canteen</div>
    <a href="{{ route('canteen.logs') }}" class="text-sm">Logs</a>
  </div>
</div>

<div class="max-w-3xl mx-auto p-4">

  <div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl p-4 shadow">
      <div class="text-xs text-gray-500 tracking-widest">TODAY SPENT</div>
      <div class="text-2xl font-extrabold text-navy mt-1">KES {{ number_format($todaySpent, 0) }}</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow">
      <div class="text-xs text-gray-500 tracking-widest">TRANSACTIONS</div>
      <div class="text-2xl font-extrabold text-navy mt-1">{{ $todayCount }}</div>
    </div>
  </div>

  {{-- SCAN BOX --}}
  <div class="bg-white rounded-2xl p-5 shadow mb-4">
    <div class="font-bold text-navy mb-3">📷 Scan Student QR</div>

    <div id="reader" class="rounded-xl overflow-hidden bg-black mb-3" style="min-height:280px;"></div>

    <div class="flex gap-2 mb-3">
      <button id="startBtn" class="flex-1 min-h-[48px] rounded-xl bg-gold text-navy font-bold">▶ Start Camera</button>
      <button id="stopBtn" class="flex-1 min-h-[48px] rounded-xl bg-red-600 text-white font-bold hidden">⏹ Stop</button>
    </div>

    <div class="flex gap-2">
      <input id="manualToken" placeholder="Or paste token..." class="flex-1 min-h-[44px] rounded-xl border-2 border-gray-200 px-3 text-sm">
      <button onclick="lookup(document.getElementById('manualToken').value)" class="px-4 rounded-xl bg-navy text-white text-sm font-semibold">Find</button>
    </div>
  </div>

  {{-- STUDENT CARD (after lookup) --}}
  <div id="studentCard" class="hidden bg-white rounded-2xl p-5 shadow mb-4"></div>

  {{-- SELL FORM --}}
  <div id="sellBox" class="hidden bg-white rounded-2xl p-5 shadow">
    <div class="font-bold text-navy mb-3">💵 Record Purchase</div>

    <div class="space-y-3">
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">QUICK ITEMS</label>
        <div class="flex flex-wrap gap-2">
          @foreach ($items as $item)
            <button type="button" onclick="addItem({{ $item->price }}, '{{ $item->name }}')"
              class="px-3 py-2 rounded-lg bg-gray-100 text-navy text-xs font-semibold hover:bg-gold">
              {{ $item->name }} · KES {{ number_format($item->price, 0) }}
            </button>
          @endforeach
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT (KES)</label>
        <input id="sellAmount" type="number" min="1" step="1" value="0"
          class="w-full min-h-[56px] rounded-xl border-2 border-gray-200 px-4 text-3xl font-bold text-center focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ITEMS / NOTE</label>
        <input id="sellItems" placeholder="e.g. Chapati + Soda" class="w-full min-h-[44px] rounded-xl border-2 border-gray-200 px-3 text-sm">
      </div>

      <button onclick="sell()" class="w-full min-h-[56px] rounded-xl bg-green-600 text-white font-bold text-lg">✅ Record Purchase</button>
    </div>
  </div>

  {{-- RESULT --}}
  <div id="result" class="hidden mt-4"></div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let scanner = null, running = false;
let currentStudent = null;

const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const studentCard = document.getElementById('studentCard');
const sellBox = document.getElementById('sellBox');
const result = document.getElementById('result');

function showResult(html) {
  result.classList.remove('hidden');
  result.innerHTML = html;
  result.scrollIntoView({ behavior: 'smooth' });
}

async function lookup(token) {
  if (!token || !token.trim()) return;
  try {
    const r = await fetch('{{ route("canteen.lookup") }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({ token: token.trim() })
    });
    const d = await r.json();

    if (d.status === 'invalid') {
      showResult('<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">' + d.message + '</div></div>');
      sellBox.classList.add('hidden');
      studentCard.classList.add('hidden');
      return;
    }

    currentStudent = d.student;
    studentCard.classList.remove('hidden');
    studentCard.innerHTML = `
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-navy text-white flex items-center justify-center font-bold text-2xl">${d.student.name.charAt(0)}</div>
        <div class="flex-1">
          <div class="font-extrabold text-navy text-xl">${d.student.name}</div>
          <div class="text-xs text-gray-500">${d.student.adm_no} · ${d.student.class}</div>
        </div>
        <div class="text-right">
          <div class="text-xs text-gray-500">WALLET</div>
          <div class="text-2xl font-extrabold ${d.student.raw_balance > 100 ? 'text-green-600' : 'text-red-600'}">KES ${d.student.balance}</div>
        </div>
      </div>`;

    sellBox.classList.remove('hidden');
    document.getElementById('sellAmount').value = 0;
    document.getElementById('sellItems').value = '';
    result.classList.add('hidden');
  } catch (e) {
    showResult('<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">Network error. Try again.</div></div>');
  }
}

function addItem(price, name) {
  const amount = document.getElementById('sellAmount');
  const items = document.getElementById('sellItems');
  amount.value = parseInt(amount.value || 0) + price;
  items.value = items.value ? items.value + ' + ' + name : name;
}

async function sell() {
  if (!currentStudent) return;
  const amt = parseFloat(document.getElementById('sellAmount').value);
  if (!amt || amt <= 0) { alert('Enter amount'); return; }

  if (amt > currentStudent.raw_balance) {
    if (!confirm('⚠️ Insufficient balance. Student has KES ' + currentStudent.balance + '. Continue anyway?')) return;
  }

  try {
    const r = await fetch('{{ route("canteen.sell") }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({
        student_id: currentStudent.id,
        amount: amt,
        items: document.getElementById('sellItems').value
      })
    });
    const d = await r.json();
    if (d.status === 'ok') {
      showResult(`
        <div class="bg-green-50 border-2 border-green-400 rounded-2xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center text-2xl">✓</div>
            <div>
              <div class="font-extrabold text-green-700 text-xl">PURCHASE OK</div>
              <div class="text-xs text-gray-600">${d.receipt_no}</div>
            </div>
          </div>
          <div class="text-sm"><strong>Student:</strong> ${currentStudent.name}</div>
          <div class="text-sm"><strong>New balance:</strong> KES ${d.new_balance}</div>
        </div>
      `);
      if (navigator.vibrate) navigator.vibrate(150);
      // Reset
      currentStudent = null;
      studentCard.classList.add('hidden');
      sellBox.classList.add('hidden');
    } else {
      showResult('<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">' + d.message + '</div></div>');
    }
  } catch (e) {
    showResult('<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">Network error</div></div>');
  }
}

startBtn.addEventListener('click', async () => {
  if (running) return;
  scanner = new Html5Qrcode("reader");
  try {
    await scanner.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } },
      (decoded) => { lookup(decoded); if (navigator.vibrate) navigator.vibrate(80); }, () => {});
    running = true;
    startBtn.classList.add('hidden');
    stopBtn.classList.remove('hidden');
  } catch (e) { alert('Camera failed. Use manual token.'); }
});

stopBtn.addEventListener('click', async () => {
  if (scanner && running) { await scanner.stop(); running = false; startBtn.classList.remove('hidden'); stopBtn.classList.add('hidden'); }
});
</script>
</body>
</html>
