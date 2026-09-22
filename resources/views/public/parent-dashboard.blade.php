@extends('layouts.public')
@section('title', 'Parent Dashboard')
@section('content')

<section class="bg-navy text-white py-10">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="text-gold font-bold tracking-widest text-xs">PARENT PORTAL</div>
        <h1 class="text-2xl md:text-3xl font-extrabold mt-1">Welcome, {{ $selected->parent_name }}</h1>
        <div class="text-white/70 text-sm mt-1">📱 {{ $phone }}</div>
      </div>
      <form method="POST" action="{{ route('parent.logout') }}">
        @csrf
        <button class="btn btn-outline text-sm !min-h-[42px]">Log Out</button>
      </form>
    </div>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-8">

  {{-- CHILDREN SWITCHER --}}
  @if ($children->count() > 1)
    <div class="mb-6">
      <div class="text-xs text-gray-500 font-semibold tracking-widest mb-2">SWITCH CHILD</div>
      <div class="flex flex-wrap gap-2">
        @foreach ($children as $c)
          <a href="{{ route('parent.dashboard', ['child' => $c->id]) }}"
             class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $c->id === $selected->id ? 'bg-navy text-white' : 'bg-gray-200 text-navy hover:bg-gray-300' }}">
            {{ $c->name }} · {{ $c->class }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- CHILD OVERVIEW CARD --}}
  <div class="card p-6 mb-6" data-aos="fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="w-16 h-16 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-2xl flex-shrink-0">
        {{ strtoupper(substr($selected->name, 0, 1)) }}
      </div>
      <div class="flex-1">
        <div class="text-2xl font-extrabold text-navy">{{ $selected->name }}</div>
        <div class="text-sm text-gray-600">{{ $selected->adm_no }} · {{ $selected->class }} {{ $selected->stream }}</div>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mt-6">
      <div class="bg-gray-50 rounded-xl p-4 text-center">
        <div class="text-xs text-gray-500 tracking-widest">TOTAL FEE</div>
        <div class="text-lg md:text-xl font-bold text-navy mt-1">KES {{ number_format((float) $selected->total_fee, 0) }}</div>
      </div>
      <div class="bg-green-50 rounded-xl p-4 text-center">
        <div class="text-xs text-gray-500 tracking-widest">PAID</div>
        <div class="text-lg md:text-xl font-bold text-green-700 mt-1">KES {{ number_format((float) $selected->paid_amount, 0) }}</div>
      </div>
      <div class="bg-red-50 rounded-xl p-4 text-center">
        <div class="text-xs text-gray-500 tracking-widest">BALANCE</div>
        <div class="text-lg md:text-xl font-bold text-red-600 mt-1">KES {{ number_format((float) $selected->balance, 0) }}</div>
      </div>
    @if ($selected->discount_amount > 0)
    <div class="bg-gold/20 rounded-xl p-4 text-center col-span-3 border-2 border-gold">
      <div class="text-xs text-navy tracking-widest">SIBLING DISCOUNT APPLIED</div>
      <div class="text-lg md:text-xl font-bold text-navy mt-1">- KES {{ number_format((float) $selected->discount_amount, 0) }} <span class="text-xs font-semibold">(child #{{ $selected->sibling_order }})</span></div>
    </div>
    @endif
    </div>

    {{-- PIE VISUAL (CSS donut) --}}
    @php
      $pct = $selected->total_fee > 0 ? round(($selected->paid_amount / $selected->total_fee) * 100) : 0;
    @endphp
    <div class="mt-6">
      <div class="flex justify-between text-xs font-semibold mb-2">
        <span class="text-gray-600">Fee Progress</span>
        <span class="text-navy">{{ $pct }}% Paid</span>
      </div>
      <div class="w-full h-3 rounded-full bg-gray-200 overflow-hidden">
        <div class="h-full bg-gradient-to-r from-navy to-gold" style="width: {{ $pct }}%"></div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-6">
      <a href="{{ route('pay') }}?adm={{ $selected->adm_no }}" class="btn btn-gold text-sm">💳 Pay Fees</a>
      <a href="#results" class="btn btn-navy text-sm">📊 View Results</a>
      <a href="#payments" class="btn bg-gray-200 text-navy text-sm">📄 Receipts</a>
      <a href="{{ route('bursar.statement', $selected) }}" class="btn bg-gray-200 text-navy text-sm">📋 Full Statement</a>
      <a href="/timetable" class="btn bg-gray-200 text-navy text-sm">📅 Timetable</a>
      <a href="/report" class="btn bg-gray-200 text-navy text-sm">🕊️ Report Concern</a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- RESULTS --}}
    <div id="results" class="card p-6" data-aos="fade-up">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-extrabold text-navy text-lg">📊 Recent Results</h2>
        <span class="text-xs text-gray-500">{{ $results->count() }} subjects</span>
      </div>

      @if ($results->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-100 text-navy">
              <tr><th class="p-2 text-left">Subject</th><th class="p-2 text-center">Marks</th><th class="p-2 text-center">Grade</th></tr>
            </thead>
            <tbody class="divide-y">
              @foreach ($results as $r)
                <tr>
                  <td class="p-2 font-semibold">{{ $r->subject }}</td>
                  <td class="p-2 text-center font-bold">{{ $r->marks }}</td>
                  <td class="p-2 text-center">
                    <span class="inline-flex items-center justify-center min-w-[36px] h-7 rounded-full font-bold text-xs
                      @if(in_array($r->grade,['A','A-'])) bg-green-100 text-green-700
                      @elseif(in_array($r->grade,['B+','B','B-'])) bg-blue-100 text-blue-700
                      @elseif(in_array($r->grade,['C+','C','C-'])) bg-yellow-100 text-yellow-700
                      @else bg-red-100 text-red-700 @endif">{{ $r->grade }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-8 text-gray-500 text-sm">
          <div class="text-3xl mb-2">📭</div>
          No results published yet.
        </div>
      @endif
    </div>

    {{-- PAYMENT HISTORY --}}
    <div id="payments" class="card p-6" data-aos="fade-up" data-aos-delay="100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-extrabold text-navy text-lg">📄 Recent Payments</h2>
        <span class="text-xs text-gray-500">Last 5</span>
      </div>

      @if ($payments->count())
        <div class="space-y-3">
          @foreach ($payments as $p)
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
              <div>
                <div class="font-semibold text-navy text-sm">KES {{ number_format((float) $p->amount, 2) }}</div>
                <div class="text-xs text-gray-500">{{ $p->created_at->format('d M Y') }} · {{ $p->transaction_code ?: $p->method }}</div>
              </div>
              <a href="{{ URL::signedRoute('pay.receipt', ['payment' => $p->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">📥 Receipt</a>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-8 text-gray-500 text-sm">
          <div class="text-3xl mb-2">💸</div>
          No payments yet.
        </div>
      @endif
    </div>

  </div>

  {{-- QUICK INFO --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
    <div class="card p-4 text-center" data-aos="fade-up">
      <div class="text-2xl mb-1">📅</div>
      <div class="text-xs text-gray-500">Timetable</div>
      <div class="text-xs font-bold text-navy mt-1">Coming soon</div>
    </div>
    <div class="card p-4 text-center" data-aos="fade-up">
      <div class="text-2xl mb-1">📚</div>
      <div class="text-xs text-gray-500">Homework</div>
      <div class="text-xs font-bold text-navy mt-1">Coming soon</div>
    </div>
    <div class="card p-4 text-center" data-aos="fade-up">
      <div class="text-2xl mb-1">📢</div>
      <div class="text-xs text-gray-500">News &amp; Events</div>
      <div class="text-xs font-bold text-navy mt-1"><a href="/news" class="text-navy underline">View all</a></div>
    </div>
    <div class="card p-4 text-center" data-aos="fade-up">
      <div class="text-2xl mb-1">💰</div>
      <div class="text-xs text-gray-500">Fee Statement</div>
      <div class="text-xs font-bold text-navy mt-1">Coming soon</div>
    </div>
  </div>
</section>

<div class="max-w-6xl mx-auto px-4 pb-8">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
    <a href="{{ route('parent.wallet') }}" class="card p-4 text-center hover:shadow-lg transition border-2 border-gold">
      <div class="text-2xl">💰</div>
      <div class="text-sm font-semibold text-navy mt-1">Canteen Wallet</div>
      <div class="text-xs text-gray-500">Load money for food</div>
    </a>
    <a href="{{ route('parent.library') }}" class="card p-4 text-center hover:shadow-lg transition border-2 border-navy">
      <div class="text-2xl">📚</div>
      <div class="text-sm font-semibold text-navy mt-1">Library Books</div>
      <div class="text-xs text-gray-500">See what kids borrowed</div>
    </a>
    <a href="{{ route('parent.pickups') }}" class="card p-4 text-center hover:shadow-lg transition border-2 border-gold">
      <div class="text-2xl">👥</div>
      <div class="text-sm font-semibold text-navy mt-1">Approved Pickups</div>
      <div class="text-xs text-gray-500">Who can collect</div>
    </a>
    <a href="/report" class="card p-4 text-center hover:shadow-lg transition">
      <div class="text-2xl">🕊️</div>
      <div class="text-sm font-semibold text-navy mt-1">Report a Concern</div>
      <div class="text-xs text-gray-500">Anonymous to Director</div>
    </a>
    <a href="/verify-receipt/REC-2026-00003" class="card p-4 text-center hover:shadow-lg transition">
      <div class="text-2xl">✓</div>
      <div class="text-sm font-semibold text-navy mt-1">Verify a Receipt</div>
      <div class="text-xs text-gray-500">Scan any receipt QR</div>
    </a>
    <a href="/contact" class="card p-4 text-center hover:shadow-lg transition">
      <div class="text-2xl">📞</div>
      <div class="text-sm font-semibold text-navy mt-1">Contact School</div>
      <div class="text-xs text-gray-500">Call, email, or message</div>
    </a>
  </div>
</div>

@endsection