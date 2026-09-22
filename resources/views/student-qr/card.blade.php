@extends('layouts.admin')
@section('title', 'Student QR Card')
@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-extrabold text-navy">📱 Student QR Card</h1>
  <p class="text-sm text-gray-500">Print this card for the student</p>
</div>

<div class="max-w-md mx-auto">
  <div class="bg-white rounded-2xl p-8 shadow-xl border-4 border-navy text-center">
    <div class="text-xs tracking-widest font-bold text-gold mb-1">MARELL ACADEMY</div>
    <div class="font-extrabold text-navy text-2xl mb-1">{{ $student->name }}</div>
    <div class="text-sm text-gray-500 mb-6">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>

    <div class="bg-white rounded-xl p-4 border-2 border-navy inline-block mb-6">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={{ urlencode($student->qrUrl()) }}" class="w-56 h-56">
    </div>

    <div class="text-xs text-gray-500">
      Scan to:<br>
      🍔 Pay at canteen · 📚 Borrow library books<br>
      🚌 Board bus · 🚪 Enter school
    </div>
  </div>

  <div class="flex gap-2 mt-4">
    <button onclick="window.print()" class="flex-1 min-h-[48px] rounded-xl bg-navy text-white font-bold">🖨️ Print</button>
    <a href="https://wa.me/?text={{ urlencode('Student QR card for ' . $student->name . ': ' . $student->qrUrl()) }}" target="_blank" class="flex-1 min-h-[48px] rounded-xl bg-green-500 text-white font-bold text-center leading-[48px]">💬 WhatsApp</a>
  </div>
</div>
@endsection
