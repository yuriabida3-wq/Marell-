@extends('layouts.public')
@section('title', 'Pay Fees Online')
@section('content')

<section class="bg-navy text-white py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">PARENT PORTAL</div>
    <h1 class="text-2xl md:text-4xl font-extrabold mt-2">Pay Fees Online</h1>
    <p class="mt-3 text-white/80 max-w-xl mx-auto text-sm md:text-base">Enter your child's admission number and pay securely via M-Pesa.</p>
  </div>
</section>

<section class="max-w-xl mx-auto px-4 py-10">

  @if (session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
  @endif

  {{-- STEP 1: SEARCH ADM --}}
  <div class="card p-6 md:p-8" data-aos="fade-up">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-bold text-sm">1</div>
      <h2 class="text-xl font-extrabold text-navy">Find Student</h2>
    </div>

    <form method="GET" action="{{ route('pay') }}" class="flex flex-col sm:flex-row gap-3">
      <x-input name="adm" wrapper-class="flex-1"
               value="{{ old('adm', request('adm', $student['adm_no'] ?? '')) }}"
               required placeholder="e.g. MAR-2024-0001" />
      <x-button variant="navy" type="submit" icon="magnifying-glass" class="w-full sm:w-auto">Find</x-button>
    </form>
  </div>

  @if ($student)
    {{-- STUDENT SUMMARY (masked) --}}
    <div class="card p-6 mt-6" data-aos="fade-up">
      <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-xl flex-shrink-0">
          {{ strtoupper(substr($student['initials'], 0, 1)) }}
        </div>
        <div class="flex-1">
          <div class="font-bold text-navy text-lg">{{ $student['initials'] }}</div>
          <div class="text-sm text-gray-600">{{ $student['adm_no'] }} · {{ $student['class'] }}</div>
          <div class="text-xs text-gray-500 mt-1">Is this your child? Full details appear on your receipt after payment.</div>
        </div>
      </div>
    </div>

    {{-- STEP 2: PAY --}}
    <div class="card p-6 md:p-8 mt-6" data-aos="fade-up">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-bold text-sm">2</div>
        <h2 class="text-xl font-extrabold text-navy">Pay via M-Pesa</h2>
      </div>

      <form method="POST" action="{{ route('pay.initiate') }}" id="payForm" class="space-y-4">
        @csrf
        <input type="hidden" name="adm" value="{{ $student['adm_no'] }}">

        <div>
          <label class="block text-sm font-semibold text-navy mb-2">Phone Number (M-Pesa) *</label>
          <input name="phone" value="{{ old('phone') }}" required placeholder="07XXXXXXXX"
                 class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <div class="text-xs text-gray-500 mt-1">We'll send an STK Push prompt to this number. The receipt will be sent to the parent number on file.</div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-navy mb-2">Amount (KES) *</label>
          <input name="amount" type="number" min="1" max="500000" step="1" required value="{{ old('amount') }}"
                 class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        </div>

        <button type="submit" id="payBtn" class="btn btn-gold w-full">
          <span id="btnText">💳 Pay via M-Pesa</span>
          <span id="btnSpinner" class="hidden items-center gap-2">
            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            Sending prompt...
          </span>
        </button>
      </form>

      <div class="mt-4 text-xs text-gray-500 text-center">
        🔒 Secure payment processed by Safaricom Daraja. You'll receive an SMS receipt from Marell Academy.
      </div>
    </div>
  @endif

  <div class="mt-8 text-center text-sm text-gray-600">
    <p>Need help? Call <a href="tel:+254700000000" class="text-navy font-semibold">+254 700 000 000</a></p>
  </div>
</section>

@push('scripts')
<script>
  const form = document.getElementById('payForm');
  if (form) {
    form.addEventListener('submit', () => {
      document.getElementById('payBtn').disabled = true;
      document.getElementById('payBtn').classList.add('opacity-80');
      document.getElementById('btnText').classList.add('hidden');
      const sp = document.getElementById('btnSpinner');
      sp.classList.remove('hidden');
      sp.classList.add('inline-flex');
    });
  }
</script>
@endpush

@endsection