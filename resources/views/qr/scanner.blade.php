@extends(role_layout())
@section('title', 'QR Receipt Scanner')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">📷 QR Receipt Scanner</h1>
  <p class="text-sm text-gray-500 mt-1">Scan any receipt QR to verify instantly. Or type the receipt number.</p>
</div>

{{-- RESULT BOX --}}
<div id="resultBox" class="hidden mb-6"></div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  {{-- CAMERA SCANNER --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📷 Scan with Camera</h2>

    <div id="reader" class="rounded-xl overflow-hidden bg-gray-100" style="min-height:300px;"></div>

    <div class="flex gap-2 mt-4">
      <button id="startBtn" class="flex-1 min-h-[48px] rounded-xl bg-gold text-navy font-bold">▶ Start Camera</button>
      <button id="stopBtn" class="flex-1 min-h-[48px] rounded-xl bg-gray-200 text-navy font-bold hidden">⏹ Stop</button>
    </div>

    <div class="text-xs text-gray-500 mt-3">
      Point the camera at the receipt QR code. It will detect automatically.
    </div>
  </div>

  {{-- MANUAL ENTRY --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">⌨️ Type Receipt Number</h2>

    <form id="manualForm" class="space-y-4" onsubmit="event.preventDefault(); verifyReceipt(document.getElementById('manualInput').value);">
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">RECEIPT NUMBER</label>
        <input id="manualInput" placeholder="e.g. REC-2026-00003" autocomplete="off"
               class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
      </div>
      <button class="w-full min-h-[52px] rounded-xl bg-navy text-white font-bold">🔍 Verify Receipt</button>
    </form>

    <div class="mt-6 bg-navy/5 rounded-xl p-4 text-xs text-navy">
      <strong>Where to find the receipt number?</strong><br>
      On any printed receipt PDF — bottom right, starts with <span class="font-mono">REC-YYYY-XXXXX</span>. Or scan the QR code.
    </div>
  </div>
</div>

{{-- SCRIPT --}}
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let scannerRunning = false;

const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const resultBox = document.getElementById('resultBox');

function showResult(html) {
    resultBox.classList.remove('hidden');
    resultBox.innerHTML = html;
    resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function verifyReceipt(input) {
    if (!input || !input.trim()) return;

    showResult(`<div class="bg-blue-50 border-l-4 border-navy p-4 rounded-lg"><div class="font-bold text-navy">⏳ Verifying...</div></div>`);

    try {
        const res = await fetch('{{ route("qr.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ receipt_no: input.trim() })
        });
        const d = await res.json();
        renderResult(d);
    } catch (e) {
        showResult(`<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg"><div class="font-bold text-red-700">❌ Network error. Try again.</div></div>`);
    }
}

function renderResult(d) {
    if (d.status === 'verified') {
        const r = d.data;
        showResult(`
            <div class="bg-green-50 border-2 border-green-400 rounded-2xl p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-full bg-green-500 text-white flex items-center justify-center text-3xl">✓</div>
                    <div>
                        <div class="text-2xl font-extrabold text-green-700">VERIFIED</div>
                        <div class="text-sm text-gray-600">Genuine Marell Academy receipt</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                    <div><div class="text-xs text-gray-500">Receipt No</div><div class="font-mono font-bold text-navy">${d.receipt}</div></div>
                    <div><div class="text-xs text-gray-500">Amount</div><div class="font-bold text-green-700 text-lg">KES ${r.amount}</div></div>
                    <div><div class="text-xs text-gray-500">Student</div><div class="font-semibold">${r.student_name}</div></div>
                    <div><div class="text-xs text-gray-500">ADM No</div><div class="font-mono text-xs">${r.adm_no}</div></div>
                    <div><div class="text-xs text-gray-500">Class</div><div>${r.class}</div></div>
                    <div><div class="text-xs text-gray-500">Method</div><div>${r.method}</div></div>
                    <div><div class="text-xs text-gray-500">Transaction</div><div class="font-mono text-xs">${r.transaction_code}</div></div>
                    <div><div class="text-xs text-gray-500">Date</div><div>${r.date_issued}</div></div>
                    <div class="col-span-2"><div class="text-xs text-gray-500">Recorded By</div><div>${r.recorded_by}</div></div>
                </div>
            </div>
        `);
        if (navigator.vibrate) navigator.vibrate(200);
    } else if (d.status === 'pending') {
        showResult(`
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-2xl p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-yellow-500 text-white flex items-center justify-center text-3xl">!</div>
                    <div>
                        <div class="text-2xl font-extrabold text-yellow-700">PENDING</div>
                        <div class="text-sm text-gray-600">${d.message}</div>
                    </div>
                </div>
            </div>
        `);
    } else {
        showResult(`
            <div class="bg-red-50 border-2 border-red-400 rounded-2xl p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-red-500 text-white flex items-center justify-center text-3xl">✕</div>
                    <div>
                        <div class="text-2xl font-extrabold text-red-700">NOT VERIFIED</div>
                        <div class="text-sm text-gray-600">${d.message}</div>
                        <div class="text-xs text-gray-500 mt-2">⚠️ This may be a fake receipt. Report to the Director immediately.</div>
                    </div>
                </div>
            </div>
        `);
        if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
    }
}

startBtn.addEventListener('click', async () => {
    if (scannerRunning) return;
    html5QrCode = new Html5Qrcode("reader");
    try {
        await html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                // QR contains URL like https://marell.ac.ke/verify-receipt/REC-XXXX
                // Or plain receipt number
                verifyReceipt(decodedText);
                if (navigator.vibrate) navigator.vibrate(100);
            },
            () => {}
        );
        scannerRunning = true;
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
    } catch (err) {
        alert('Camera access denied or unavailable. Use manual entry below.\n\nNote: camera needs HTTPS or localhost.');
    }
});

stopBtn.addEventListener('click', async () => {
    if (html5QrCode && scannerRunning) {
        await html5QrCode.stop();
        scannerRunning = false;
        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');
    }
});
</script>
@endpush

@endsection
