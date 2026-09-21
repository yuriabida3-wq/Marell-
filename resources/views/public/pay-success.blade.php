@extends('layouts.public')
@section('title', 'Payment Successful')
@section('content')

<section class="max-w-lg mx-auto px-4 py-12">
  <div class="card p-8 text-center" data-aos="fade-up" id="successCard">

    {{-- GREEN TICK --}}
    <div class="flex justify-center mb-6">
      <div class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center animate-bounce-in">
        <svg class="w-14 h-14 text-green-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      </div>
    </div>

    <h1 class="text-3xl font-extrabold text-navy">Payment Successful!</h1>
    <p class="text-gray-600 mt-3">Thank you. Your payment has been received.</p>

    <div class="mt-6 bg-green-50 rounded-xl p-5">
      <div class="text-xs text-gray-500 tracking-widest">AMOUNT PAID</div>
      <div class="text-4xl font-extrabold text-green-700 mt-1">KES {{ number_format($payment->amount, 2) }}</div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-3 text-left">
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Student</div>
        <div class="font-bold text-navy text-sm">{{ $payment->student->name }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">ADM No</div>
        <div class="font-bold text-navy text-sm">{{ $payment->student->adm_no }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Transaction Code</div>
        <div class="font-bold text-navy text-sm">{{ $payment->transaction_code ?: '—' }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Receipt No</div>
        <div class="font-bold text-navy text-sm">{{ $payment->receipt_no }}</div>
      </div>
      <div class="bg-red-50 rounded-xl p-3 col-span-2">
        <div class="text-xs text-gray-500">New Balance</div>
        <div class="font-bold text-red-600 text-lg">KES {{ number_format($payment->student->balance, 2) }}</div>
      </div>
    </div>

    {{-- ACTIONS --}}
    <div class="mt-6 space-y-3">
      <a href="{{ URL::signedRoute('pay.receipt', ['payment' => $payment->id]) }}" class="btn btn-gold w-full">📄 Download Receipt (PDF)</a>

      <div class="flex gap-3">
        <a href="https://wa.me/254700000000?text={{ urlencode('Hello Marell. I just paid KES '.number_format($payment->amount).' for '.$payment->student->name.'. Receipt '.$payment->receipt_no) }}"
           target="_blank" class="btn bg-green-600 text-white flex-1 text-sm">💬 WhatsApp</a>
        <a href="{{ route('pay') }}" class="btn btn-navy flex-1 text-sm">💳 Pay Another</a>
      </div>
    </div>

    <div class="mt-4 text-xs">
      <a href="/verify-receipt/{{ $payment->receipt_no }}" class="text-navy underline">✓ Verify this receipt online</a>
    </div>

<div class="mt-6 text-xs text-gray-500">
      An SMS receipt has been sent to {{ $payment->student->parent_phone }}.
    </div>
  </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
  window.addEventListener('load', () => {
    const duration = 2500;
    const end = Date.now() + duration;
    (function frame() {
      confetti({
        particleCount: 4,
        angle: 60,
        spread: 60,
        origin: { x: 0 },
        colors: ['#0B3D91', '#D4AF37', '#ffffff']
      });
      confetti({
        particleCount: 4,
        angle: 120,
        spread: 60,
        origin: { x: 1 },
        colors: ['#0B3D91', '#D4AF37', '#ffffff']
      });
      if (Date.now() < end) requestAnimationFrame(frame);
    })();
  });
</script>
<style>
  @keyframes bounce-in {
    0%   { transform: scale(0); opacity: 0; }
    60%  { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); }
  }
  .animate-bounce-in { animation: bounce-in .6s ease-out; }
</style>
@endpush

@endsection
