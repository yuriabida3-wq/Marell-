@extends('layouts.public')
@section('title', 'Waiting for M-Pesa...')
@section('content')

<section class="max-w-lg mx-auto px-4 py-16 text-center">
  <div class="card p-8" data-aos="fade-up">
    <div class="flex justify-center mb-6">
      <div class="w-20 h-20 rounded-full bg-gold/20 flex items-center justify-center">
        <svg class="animate-spin h-10 w-10 text-gold" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
      </div>
    </div>

    <h1 class="text-2xl font-extrabold text-navy">Waiting for M-Pesa</h1>
    <p class="text-gray-600 mt-3">An STK Push prompt has been sent to your phone. Enter your M-Pesa PIN to complete the payment.</p>

    <div class="mt-6 bg-gray-50 rounded-xl p-4 text-sm">
      <div class="text-gray-500">Amount</div>
      <div class="font-bold text-navy text-xl">KES {{ number_format($payment->amount, 0) }}</div>
    </div>

    <div class="mt-6 flex items-center justify-center gap-2 text-xs text-gray-500">
      <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
      Checking payment status...
    </div>

    <div class="mt-6">
      <a href="{{ route('pay') }}" class="text-sm text-navy underline">Cancel and try another payment</a>
    </div>
  </div>
</section>

@push('scripts')
<script>
  const checkUrl = "{{ route('pay.check', $payment) }}";
  let attempts = 0;
  const maxAttempts = 60; // 2 minutes

  const iv = setInterval(async () => {
    attempts++;
    try {
      const res = await fetch(checkUrl, { headers: { 'Accept': 'application/json' } });
      const data = await res.json();

      if (data.status === 'completed' && data.redirect) {
        clearInterval(iv);
        window.location.href = data.redirect;
      } else if (data.status === 'failed') {
        clearInterval(iv);
        alert('Payment failed or was cancelled. Please try again.');
        window.location.href = "{{ route('pay') }}";
      }

      if (attempts > maxAttempts) {
        clearInterval(iv);
        alert('Still waiting... The payment might take a bit longer. Check your phone.');
      }
    } catch (e) {
      // ignore network blip, keep polling
    }
  }, 2000);
</script>
@endpush

@endsection
