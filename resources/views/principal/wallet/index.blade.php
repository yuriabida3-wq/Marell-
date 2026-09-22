@extends('layouts.admin')
@section('title', 'Canteen Wallet')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">💰 Canteen Wallet</h1>
  <p class="text-sm text-gray-500 mt-1">Manage student wallets, print QR cards, monitor cashless canteen</p>
</div>

@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>@endif
@if (session('error'))<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>@endif

<div class="grid grid-cols-3 gap-3 mb-6">
  <div class="bg-navy text-white rounded-2xl p-5 shadow">
    <div class="text-xs tracking-widest text-gold font-bold">TOTAL IN WALLETS</div>
    <div class="text-2xl font-extrabold mt-1">KES {{ number_format($totalWallets, 0) }}</div>
  </div>
  <div class="bg-green-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-bold">LOADED TODAY</div>
    <div class="text-2xl font-extrabold text-green-700 mt-1">KES {{ number_format($todayLoad, 0) }}</div>
  </div>
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-bold">SPENT TODAY</div>
    <div class="text-2xl font-extrabold text-red-600 mt-1">KES {{ number_format($todaySpend, 0) }}</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <div class="lg:col-span-1">
    <div class="bg-white rounded-2xl p-6 shadow sticky top-20">
      <h2 class="font-extrabold text-navy mb-4">➕ Manual Load</h2>
      <form method="POST" action="{{ route('principal.wallet.load') }}" class="space-y-3">
        @csrf
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STUDENT (search)</label>
          <input id="studentSearch" placeholder="Name or ADM..." class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
          <div id="searchResults" class="mt-2 max-h-40 overflow-y-auto"></div>
        </div>
        <input type="hidden" name="student_id" id="studentId">
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT (KES)</label>
          <input name="amount" type="number" min="1" value="500" required class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-3">
        </div>
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NOTES</label>
          <input name="notes" placeholder="Cash received at office" class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        </div>
        <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">💰 Load Wallet</button>
      </form>
    </div>
  </div>

  <div class="lg:col-span-2">
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div class="p-5 border-b flex items-center justify-between">
        <div class="font-extrabold text-navy">📋 Students & Balances</div>
        <a href="{{ route('canteen.index') }}" class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg font-semibold">🍔 Open Canteen</a>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-500">
            <tr class="text-left">
              <th class="p-3">ADM</th>
              <th class="p-3">NAME</th>
              <th class="p-3">CLASS</th>
              <th class="p-3 text-right">WALLET</th>
              <th class="p-3 text-right">QR</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse ($students as $s)
              <tr class="hover:bg-gray-50">
                <td class="p-3 font-mono text-xs">{{ $s->adm_no }}</td>
                <td class="p-3 font-semibold text-navy">{{ $s->name }}</td>
                <td class="p-3 text-gray-600">{{ $s->class }}</td>
                <td class="p-3 text-right font-bold {{ $s->wallet_balance > 0 ? 'text-green-600' : 'text-gray-400' }}">KES {{ number_format((float) $s->wallet_balance, 0) }}</td>
                <td class="p-3 text-right">
                  <a href="{{ route('student-qr-cards.card', $s) }}" class="text-xs font-semibold text-navy hover:text-gold">📱 Card</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="p-8 text-center text-gray-400">No students</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>

    <div class="bg-white rounded-2xl p-5 shadow mt-4">
      <h3 class="font-extrabold text-navy mb-3">🖨️ Bulk Print QR Cards</h3>
      <form method="POST" action="{{ route('student-qr-cards.bulk-pdf') }}" class="flex flex-wrap gap-2">
        @csrf
        <input name="class" placeholder="Class name e.g. Class 6" required class="flex-1 min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        <input name="stream" placeholder="Stream (optional)" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        <button class="px-4 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">📄 Generate PDF</button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
const input = document.getElementById('studentSearch');
const results = document.getElementById('searchResults');
const studentId = document.getElementById('studentId');
let timer;

input.addEventListener('input', () => {
  clearTimeout(timer);
  const q = input.value.trim();
  if (q.length < 2) { results.innerHTML = ''; return; }
  timer = setTimeout(async () => {
    const r = await fetch('{{ route("principal.wallet.search") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
    const d = await r.json();
    results.innerHTML = d.results.map(x =>
      `<div onclick="pick(${x.id}, '${x.name.replace(/'/g, "\\'")}')" class="p-2 rounded bg-gray-50 hover:bg-gold cursor-pointer text-xs">
        <div class="font-semibold text-navy">${x.name}</div>
        <div class="text-gray-500">${x.adm_no} · ${x.class} · KES ${x.balance}</div>
      </div>`
    ).join('');
  }, 300);
});

function pick(id, name) {
  studentId.value = id;
  input.value = name;
  results.innerHTML = '';
}
</script>
@endpush
@endsection
