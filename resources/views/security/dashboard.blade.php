<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gate Control</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'},fontFamily:{poppins:['Poppins','sans-serif'],inter:['Inter','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif}h1{font-family:Poppins,sans-serif;font-weight:800}</style>
</head>
<body class="min-h-screen bg-gray-100 pb-24">

<div class="bg-navy text-white p-5 shadow-lg">
  <div class="flex items-center justify-between max-w-2xl mx-auto">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-full bg-gold text-navy flex items-center justify-center font-bold text-xl">M</div>
      <div>
        <div class="font-bold text-lg">{{ session('guard_name') }}</div>
        <div class="text-xs text-gold font-bold tracking-widest">SECURITY GATE</div>
      </div>
    </div>
    <form method="POST" action="{{ route('security.logout') }}">
      @csrf
      <button class="text-xs text-white/60 hover:text-white">Logout</button>
    </form>
  </div>
</div>

<div class="max-w-2xl mx-auto p-4">

  <h1 class="text-2xl text-navy mt-4 mb-4">Gate Control</h1>

  <div class="grid grid-cols-2 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 shadow">
      <div class="text-xs text-gray-500 tracking-widest font-bold">INSIDE NOW</div>
      <div class="text-3xl font-extrabold text-navy mt-1">{{ $inside }}</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow">
      <div class="text-xs text-gray-500 tracking-widest font-bold">RELEASES TODAY</div>
      <div class="text-3xl font-extrabold text-navy mt-1">{{ $todayReleases }}</div>
    </div>
    <div class="bg-green-50 rounded-2xl p-4 shadow">
      <div class="text-xs text-gray-500 tracking-widest font-bold">✅ VERIFIED</div>
      <div class="text-3xl font-extrabold text-green-700 mt-1">{{ $verified }}</div>
    </div>
    <div class="bg-red-50 rounded-2xl p-4 shadow">
      <div class="text-xs text-gray-500 tracking-widest font-bold">❌ DENIED</div>
      <div class="text-3xl font-extrabold text-red-600 mt-1">{{ $denied }}</div>
    </div>
  </div>

  @if ($openAlerts > 0)
    <div class="bg-red-600 text-white rounded-2xl p-4 mb-4 shadow-lg animate-pulse">
      <div class="font-bold">🚨 {{ $openAlerts }} OPEN PANIC ALERT(S)</div>
    </div>
  @endif

  <div class="space-y-3">
    <a href="{{ route('security.scan') }}" class="flex items-center gap-4 bg-navy text-white rounded-2xl p-6 shadow-lg hover:scale-[1.02] transition">
      <div class="text-4xl">📷</div>
      <div class="flex-1">
        <div class="font-bold text-lg">Scan Pickup QR</div>
        <div class="text-xs opacity-70">Verify approved pickups with camera</div>
      </div>
      <div class="text-2xl">→</div>
    </a>

    <a href="/canteen" class="flex items-center gap-4 bg-white rounded-2xl p-6 shadow hover:scale-[1.02] transition">
      <div class="text-4xl">🍔</div>
      <div class="flex-1">
        <div class="font-bold text-lg text-navy">Canteen</div>
        <div class="text-xs text-gray-500">Sell food with student QR</div>
      </div>
      <div class="text-2xl text-navy">→</div>
    </a>
    <a href="{{ route('security.logs') }}" class="flex items-center gap-4 bg-white rounded-2xl p-6 shadow hover:scale-[1.02] transition">
      <div class="text-4xl">📋</div>
      <div class="flex-1">
        <div class="font-bold text-lg text-navy">Pickup Logs</div>
        <div class="text-xs text-gray-500">Recent releases & denials</div>
      </div>
      <div class="text-2xl text-navy">→</div>
    </a>

    <a href="/visitors/gate" class="flex items-center gap-4 bg-white rounded-2xl p-6 shadow hover:scale-[1.02] transition">
      <div class="text-4xl">🚪</div>
      <div class="flex-1">
        <div class="font-bold text-lg text-navy">Visitor Sign-In</div>
        <div class="text-xs text-gray-500">Full visitor check-in / check-out</div>
      </div>
      <div class="text-2xl text-navy">→</div>
    </a>
  </div>

  {{-- PANIC BUTTON --}}
  <div class="mt-8">
    <button id="panicBtn" onclick="triggerPanic()"
      class="w-full py-6 rounded-2xl bg-red-600 text-white font-extrabold text-2xl shadow-2xl hover:bg-red-700 active:scale-95 transition"
      style="min-height:120px;">
      🚨 PANIC BUTTON
      <div class="text-sm font-normal mt-1 opacity-90">Tap to alert Director + Staff</div>
    </button>
  </div>
</div>

<script>
async function triggerPanic() {
  if (!confirm('🚨 SEND PANIC ALERT to Director and all staff?')) return;

  const btn = document.getElementById('panicBtn');
  btn.disabled = true;
  btn.innerHTML = '⏳ SENDING ALERT...';

  try {
    const r = await fetch('{{ route("security.panic") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ location: 'Main Gate' })
    });
    const d = await r.json();
    if (navigator.vibrate) navigator.vibrate([300, 100, 300, 100, 300]);
    if (confirm('✅ ALERT SENT\n\n' + (d.message || 'Director notified.') + '\n\nSend another?')) {
      btn.disabled = false;
      btn.innerHTML = '🚨 PANIC BUTTON<div class="text-sm font-normal mt-1 opacity-90">Tap to alert Director + Staff</div>';
    } else {
      window.location.reload();
    }
  } catch (e) {
    alert('❌ Failed to send. Call the Director directly.');
    btn.disabled = false;
    btn.innerHTML = '🚨 PANIC BUTTON';
  }
}
</script>

</body>
</html>
