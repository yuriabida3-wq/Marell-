@extends('layouts.admin')
@section('title', 'Voice SMS')
@section('content')
<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Voice SMS</h1>
  <p class="text-sm text-gray-500 mt-1">For parents who can't read — automated Swahili voice calls</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">Bulk Voice Reminder</h2>
    <div class="bg-gold/10 border border-gold rounded-xl p-4 mb-6 text-sm text-navy">
      Sends a Swahili voice call to the parents of all defaulters: "Mzazi wa Brian, salio ni shilingi 12,000. Tafadhali lipa kabla tarehe kumi."
    </div>

    <form method="POST" action="{{ route('principal.voice-sms.bulk') }}" class="space-y-4" onsubmit="return confirm('Call ALL defaulters? This will use voice credits.')">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">MINIMUM BALANCE (KES)</label>
        <input type="number" name="min_balance" value="5000" min="0" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <div class="text-xs text-gray-500 mt-1">Only call parents whose balance is above this</div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">MAX CALLS</label>
        <input type="number" name="limit" value="50" min="1" max="200" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      </div>
      <div class="bg-red-50 border-l-4 border-red-500 rounded p-3 text-xs text-red-700">
        <strong>{{ $defaulters }}</strong> students currently have an outstanding balance.
      </div>
      <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">Start Voice Campaign</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">Preview Message</h2>
    <p class="text-sm text-gray-600 mb-4">Enter an ADM to see what the parent will hear.</p>

    <div class="flex gap-2 mb-4">
      <input id="adm" placeholder="e.g. MAR-2024-0001" class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      <button onclick="preview()" class="px-4 rounded-xl bg-navy text-white text-sm font-semibold">Preview</button>
    </div>

    <div id="result" class="hidden bg-gray-50 rounded-xl p-4 text-sm"></div>
    <div id="error" class="hidden bg-red-50 border-l-4 border-red-600 rounded p-3 text-sm text-red-700"></div>
  </div>
</div>

<div class="mt-6 bg-blue-50 border-l-4 border-navy rounded-xl p-4 text-sm text-navy">
  <strong>Demo Mode:</strong> Voice calls use Africa's Talking Voice API. For now they log to the app log. To enable real calls, add your AT Voice API key (same account as SMS) — the code is already wired.
</div>

@push('scripts')
<script>
async function preview() {
  const adm = document.getElementById('adm').value.trim();
  if (!adm) return;
  document.getElementById('result').classList.add('hidden');
  document.getElementById('error').classList.add('hidden');

  const r = await fetch('{{ route("principal.voice-sms.preview") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    body: JSON.stringify({ adm })
  });
  const d = await r.json();
  if (d.error) {
    document.getElementById('error').textContent = d.error;
    document.getElementById('error').classList.remove('hidden');
    return;
  }
  document.getElementById('result').innerHTML = '<div class="text-xs text-gray-500">To: ' + d.phone + ' (' + d.student + ')</div><div class="mt-2 italic">' + d.message + '</div>';
  document.getElementById('result').classList.remove('hidden');
}
</script>
@endpush
@endsection
