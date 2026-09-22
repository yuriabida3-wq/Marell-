@extends('layouts.public')
@section('title', 'My Child QR — ' . $student->name)
@section('content')
<section class="bg-navy text-white py-8">
  <div class="max-w-2xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-xs">STUDENT QR</div>
    <h1 class="text-2xl md:text-3xl font-extrabold mt-1">{{ $student->name }}</h1>
  </div>
</section>

<section class="max-w-2xl mx-auto px-4 py-8">
  <div class="card p-8 text-center">
    <div class="bg-white border-4 border-navy rounded-2xl p-6 inline-block mb-6">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={{ urlencode($student->qrUrl()) }}" class="w-64 h-64">
    </div>

    <div class="bg-navy text-white rounded-xl p-4 mb-4">
      <div class="text-xs tracking-widest text-gold font-bold">WALLET BALANCE</div>
      <div class="text-3xl font-extrabold mt-1">KES {{ number_format((float) $student->wallet_balance, 2) }}</div>
    </div>

    <div class="text-xs text-gray-500 mb-6">
      Show this QR to the canteen, library, or bus attendant.
    </div>

    <div class="flex gap-2">
      <a href="{{ route('parent.wallet') }}" class="flex-1 btn bg-gray-100 text-navy text-sm">← Wallet</a>
      <a href="https://wa.me/?text={{ urlencode('QR for ' . $student->name . ': ' . $student->qrUrl()) }}" target="_blank" class="flex-1 btn bg-green-500 text-white text-sm">💬 Share</a>
    </div>
  </div>
</section>
@endsection
