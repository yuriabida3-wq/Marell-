@extends('layouts.public')
@section('title', 'Canteen Wallet')
@section('content')

<section class="bg-navy text-white py-10">
  <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
    <div>
      <div class="text-gold font-bold tracking-widest text-xs">PARENT PORTAL</div>
      <h1 class="text-2xl md:text-3xl font-extrabold mt-1">💰 Canteen Wallet</h1>
      <p class="text-white/70 text-sm mt-1">Load money for school meals & snacks — no cash</p>
    </div>
    <a href="{{ route('parent.dashboard') }}" class="btn btn-outline text-sm !min-h-[42px]">← Dashboard</a>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-8">

  @if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>@endif
  @if (session('error'))<div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>@endif

  @if ($children->count() > 1)
    <div class="mb-6">
      <div class="text-xs text-gray-500 font-semibold tracking-widest mb-2">SWITCH CHILD</div>
      <div class="flex flex-wrap gap-2">
        @foreach ($children as $c)
          <a href="{{ route('parent.wallet', ['child' => $c->id]) }}"
             class="px-4 py-2 rounded-xl text-sm font-semibold {{ $c->id === $selected->id ? 'bg-navy text-white' : 'bg-gray-200 text-navy' }}">
            {{ $c->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- BALANCE CARD --}}
  <div class="card p-6 mb-6 bg-gradient-to-br from-navy to-[#082f6f] text-white">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-xl">
          {{ strtoupper(substr($selected->name, 0, 1)) }}
        </div>
        <div>
          <div class="font-bold text-lg">{{ $selected->name }}</div>
          <div class="text-xs opacity-70">{{ $selected->adm_no }} · {{ $selected->class }} {{ $selected->stream }}</div>
        </div>
      </div>
      <div class="text-right">
        <div class="text-xs text-gold tracking-widest font-bold">WALLET BALANCE</div>
        <div class="text-3xl font-extrabold">KES {{ number_format($selected->wallet_balance, 2) }}</div>
      </div>
    </div>

    <div class="flex gap-2">
      <a href="{{ route('parent.wallet.qr', $selected) }}" class="flex-1 text-center min-h-[44px] rounded-xl bg-gold text-navy font-bold text-sm leading-[44px]">📱 Show QR</a>
    </div>
  </div>

  {{-- TOP UP --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="md:col-span-2 card p-6">
      <h2 class="font-extrabold text-navy mb-4">➕ Top Up Wallet</h2>
      <form method="POST" action="{{ route('parent.wallet.topup') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="student_id" value="{{ $selected->id }}">
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AMOUNT (KES)</label>
          <input name="amount" type="number" min="20" max="50000" step="10" value="500" required class="w-full min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-2xl font-bold focus:border-gold focus:outline-none">
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="button" onclick="document.querySelector('input[name=amount]').value=200" class="px-4 py-2 rounded-full bg-gray-100 text-navy text-xs font-bold">200</button>
          <button type="button" onclick="document.querySelector('input[name=amount]').value=500" class="px-4 py-2 rounded-full bg-gray-100 text-navy text-xs font-bold">500</button>
          <button type="button" onclick="document.querySelector('input[name=amount]').value=1000" class="px-4 py-2 rounded-full bg-gray-100 text-navy text-xs font-bold">1,000</button>
          <button type="button" onclick="document.querySelector('input[name=amount]').value=2000" class="px-4 py-2 rounded-full bg-gray-100 text-navy text-xs font-bold">2,000</button>
          <button type="button" onclick="document.querySelector('input[name=amount]').value=5000" class="px-4 py-2 rounded-full bg-gray-100 text-navy text-xs font-bold">5,000</button>
        </div>
        <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">💰 Load Wallet</button>
      </form>
    </div>

    <div class="card p-6">
      <h2 class="font-extrabold text-navy mb-4">🔄 Auto-Reload</h2>
      <form method="POST" action="{{ route('parent.wallet.settings') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="student_id" value="{{ $selected->id }}">

        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="auto_reload_enabled" value="1" {{ $selected->auto_reload_enabled ? 'checked' : '' }} class="w-5 h-5">
          Enable auto-reload
        </label>

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">WHEN BALANCE BELOW</label>
          <input name="auto_reload_threshold" type="number" min="0" value="{{ (int) $selected->auto_reload_threshold }}" class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">LOAD THIS MUCH</label>
          <input name="auto_reload_amount" type="number" min="20" value="{{ (int) $selected->auto_reload_amount }}" class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
        </div>

        <button class="w-full min-h-[44px] rounded-xl bg-navy text-white text-sm font-bold">Save</button>

        <div class="text-xs text-gray-500 mt-2">
          When wallet drops below threshold, we'll request M-Pesa payment automatically so your child never runs out.
        </div>
      </form>
    </div>
  </div>

  {{-- TRANSACTIONS --}}
  <div class="card overflow-hidden">
    <div class="p-5 border-b"><div class="font-extrabold text-navy">📋 Recent Transactions</div></div>
    @if ($transactions->count())
      <div class="divide-y">
        @foreach ($transactions as $t)
          <div class="p-3 flex items-center justify-between text-sm">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full {{ $t->type === 'load' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center text-lg">
                {{ $t->type === 'load' ? '⬆' : '⬇' }}
              </div>
              <div>
                <div class="font-semibold text-navy">{{ ucfirst($t->type) }}{{ $t->description ? ' — ' . $t->description : '' }}</div>
                <div class="text-xs text-gray-500">{{ $t->created_at->format('d M Y H:i') }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold {{ $t->type === 'load' ? 'text-green-600' : 'text-red-600' }}">
                {{ $t->type === 'load' ? '+' : '-' }} KES {{ number_format($t->amount, 2) }}
              </div>
              <div class="text-xs text-gray-500">Bal: KES {{ number_format($t->balance_after, 2) }}</div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="p-12 text-center text-gray-400">
        <div class="text-4xl mb-2">💰</div>
        No transactions yet. Top up to get started.
      </div>
    @endif
  </div>
</section>
@endsection
