@extends('layouts.public')
@section('title', 'Student QR')
@section('content')
<section class="min-h-screen flex items-center justify-center p-4 bg-gray-100">
  <div class="w-full max-w-md">
    @if ($status === 'valid')
      <div class="bg-white rounded-3xl p-8 shadow-2xl text-center border-t-8 border-navy">
        <div class="w-20 h-20 mx-auto rounded-full bg-navy text-white flex items-center justify-center text-4xl font-bold mb-4">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
        <div class="text-2xl font-extrabold text-navy">{{ $student->name }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>

        <div class="bg-navy text-white rounded-xl p-4 mt-6">
          <div class="text-xs tracking-widest text-gold font-bold">WALLET BALANCE</div>
          <div class="text-3xl font-extrabold mt-1">KES {{ number_format($student->wallet_balance, 2) }}</div>
        </div>

        <div class="mt-6 text-xs text-gray-500">Scan at any school facility to use.</div>
      </div>
    @else
      <div class="bg-red-600 text-white rounded-3xl p-8 shadow-2xl text-center">
        <div class="w-20 h-20 mx-auto rounded-full bg-white text-red-600 flex items-center justify-center text-5xl font-bold mb-4">✕</div>
        <div class="text-3xl font-extrabold mb-2">INVALID QR</div>
        <div class="text-sm opacity-90">This QR code is not recognized.</div>
      </div>
    @endif
  </div>
</section>
@endsection
