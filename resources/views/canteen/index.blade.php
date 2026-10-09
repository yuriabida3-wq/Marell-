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

  {{-- STUDENT CARD --}}
  <div id="studentCard" class="hidden bg-white rounded-2xl p-5 shadow mb-4"></div>

  {{-- CART --}}
  <div id="cartBox" class="hidden bg-white rounded-2xl p-5 shadow">
    <div class="font-bold text-navy mb-3">🛒 Cart</div>

    <div class="space-y-3">
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">MENU</label>
        <div class="flex flex-wrap gap-2">
          @foreach ($items as $item)
            <button type="button"
              data-id="{{ $item->id }}"
              data-name="{{ $item->name }}"
              data-price="{{ $item->price }}"
              onclick="addToCart(this)"
              class="px-3 py-2 rounded-lg bg-gray-100 text-navy text-xs font-semibold hover:bg-gold">
              {{ $item->name }} · KES {{ number_format($item->price, 0) }}
            </button>
          @endforeach
        </div>
      </div>

      <div id="cartLines" class="text-sm text-gray-700 border-t border-b py-3 space-y-1"></div>

      <div class="flex items-center justify-between">
        <div class="text-xs text-gray-500 tracking-widest">TOTAL</div>
        <div id="cartTotal" class="text-3xl font-extrabold text-navy">KES 0</div>
      </div>

      <button onclick="sell()" class="w-full min-h-[56px] rounded-xl bg-green-600 text-white font-bold text-lg">✅ Record Purchase</button>
      <button onclick="clearCart()" class="w-full min-h-[44px] rounded-xl bg-gray-100 text-navy font-semibold text-sm">Clear cart</button>
    </div>
  </div>

  {{-- RESULT --}}
  <div id="result" class="hidden mt-4"></div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let scanner = null, running = false;
let currentStudent = null;
let cart = {};

const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const studentCard = document.getElementById('studentCard');
const cartBox = document.getElementById('cartBox');
const result = document.getElementById('result');
const cartLines = document.getElementById('cartLines');
const cartTotal = document.getElementById('cartTotal');

function showResult(html) {
  result.classList.remove('hidden');
  result.innerHTML = html;
  result.scrollIntoView({ behavior: 'smooth' });
}

function renderCart() {
  cartLines.innerHTML = '';
  let total = 0;
  const ids = Object.keys(cart);
  if (ids.length === 0) {
    cartLines.innerHTML = '<div class="text-xs text-gray-400">Tap menu items to add them.</div>';
  }
  ids.forEach(id => {
    const line = cart[id];
    total += line.price * line.qty;
    const row = document.createElement('div');
    row.className = 'flex items-center justify-between';
    row.innerHTML = '<div>' + line.name + ' <span class="text-xs text-gray-500">x ' + line.qty + '</span></div>'
      + '<div class="flex items-center gap-2">'
      + '<span class="font-semibold">KES ' + (line.price * line.qty).toFixed(0) + '</span>'
      + '<button class="text-xs text-red-600 underline" onclick="removeOne(' + id + ')">minus</button>'
      + '</div>';
    cartLines.appendChild(row);
  });
  cartTotal.textContent = 'KES ' + total.toFixed(0);
}

function addToCart(btn) {
  const id = parseInt(btn.dataset.id);
  const name = btn.dataset.name;
  const price = parseFloat(btn.dataset.price);
  if (!cart[id]) cart[id] = { name: name, price: price, qty: 0 };
  cart[id].qty += 1;
  renderCart();
}

function removeOne(id) {
  if (!cart[id]) return;
  cart[id].qty -= 1;
  if (cart[id].qty <= 0) delete cart[id];
  renderCart();
}

function clearCart() {
  cart = {};
  renderCart();
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
      cartBox.classList.add('hidden');
      studentCard.classList.add('hidden');
      return;
    }

    currentStudent = d.student;
    studentCard.classList.remove('hidden');

    let info = ''
      + '<div class="flex items-center gap-4">'
      + '<div class="w-16 h-16 rounded-full bg-navy text-white flex items-center justify-center font-bold text-2xl">' + d.student.name.charAt(0) + '</div>'
      + '<div class="flex-1">'
      + '<div class="font-extrabold text-navy text-xl">' + d.student.name + '</div>'
      + '<div class="text-xs text-gray-500">' + d.student.adm_no + ' · ' + d.student.class + '</div>'
      + '</div>'
      + '<div class="text-right">'
      + '<div class="text-xs text-gray-500">WALLET</div>'
      + '<div class="text-2xl font-extrabold ' + (d.student.raw_balance > 100 ? 'text-green-600' : 'text-red-600') + '">KES ' + d.student.balance + '</div>'
      + '</div></div>';

    // Blocked items / categories warning + daily limit
    const blocked = [].concat(d.student.blocked_items || []).concat((d.student.blocked_categories || []).map(function (c) { return 'ALL ' + c; }));
    if (blocked.length > 0) {
      info += '<div class="mt-3 text-xs bg-red-50 text-red-700 border-l-4 border-red-600 p-2 rounded">Blocked: ' + blocked.join(', ') + '</div>';
    }
    if (d.student.daily_limit > 0) {
      info += '<div class="mt-2 text-xs text-gray-600">Daily limit: KES ' + d.student.daily_limit + '</div>';
    }

    studentCard.innerHTML = info;

    cart = {};
    renderCart();
    cartBox.classList.remove('hidden');
    result.classList.add('hidden');
  } catch (e) {
    showResult('<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">Network error. Try again.</div></div>');
  }
}

async function sell() {
  if (!currentStudent) return;
  const ids = Object.keys(cart).map(function (k) { return parseInt(k); });
  if (ids.length === 0) { alert('Add at least one item'); return; }

  const quantities = ids.map(function (id) { return cart[id].qty; });

  try {
    const r = await fetch('{{ route("canteen.item-checkout") }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({
        student_id: currentStudent.id,
        item_ids: ids,
        quantities: quantities
      })
    });
    const d = await r.json();

    if (d.status === 'ok') {
      showResult(''
        + '<div class="bg-green-50 border-2 border-green-400 rounded-2xl p-5">'
        + '<div class="flex items-center gap-3 mb-3">'
        + '<div class="w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center text-2xl">OK</div>'
        + '<div>'
        + '<div class="font-extrabold text-green-700 text-xl">PURCHASE OK</div>'
        + '<div class="text-xs text-gray-600">' + d.receipt_no + '</div>'
        + '</div></div>'
        + '<div class="text-sm"><strong>Student:</strong> ' + currentStudent.name + '</div>'
        + '<div class="text-sm"><strong>Total:</strong> KES ' + d.total + '</div>'
        + '<div class="text-sm"><strong>New balance:</strong> KES ' + d.new_balance + '</div>'
        + '</div>');
      if (navigator.vibrate) navigator.vibrate(150);
      currentStudent = null;
      cart = {};
      studentCard.classList.add('hidden');
      cartBox.classList.add('hidden');
    } else {
      showResult('<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">' + (d.message || 'Purchase failed') + '</div></div>');
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