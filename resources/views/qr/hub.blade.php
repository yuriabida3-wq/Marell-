@extends('layouts.admin')
@section('title', 'QR Hub')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">📱 QR Hub</h1>
  <p class="text-sm text-gray-500 mt-1">All QR codes in one place. Scan with any phone camera or the built-in scanner.</p>
</div>

{{-- QUICK LINKS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
  <a href="/security/login" class="bg-white rounded-2xl p-4 text-center shadow hover:shadow-lg transition">
    <div class="text-3xl mb-1">📷</div>
    <div class="font-bold text-navy text-sm">Guard Scanner</div>
    <div class="text-xs text-gray-500">Login: 0700900001 / PIN 1234</div>
  </a>
  <a href="/qr-scanner" class="bg-white rounded-2xl p-4 text-center shadow hover:shadow-lg transition">
    <div class="text-3xl mb-1">🔍</div>
    <div class="font-bold text-navy text-sm">Receipt Scanner</div>
    <div class="text-xs text-gray-500">Verify receipts</div>
  </a>
  <a href="/canteen" class="bg-white rounded-2xl p-4 text-center shadow hover:shadow-lg transition">
    <div class="text-3xl mb-1">🍔</div>
    <div class="font-bold text-navy text-sm">Canteen POS</div>
    <div class="text-xs text-gray-500">Sell food via QR</div>
  </a>
  <a href="/student-qr-cards/bulk-pdf" class="bg-white rounded-2xl p-4 text-center shadow hover:shadow-lg transition" onclick="event.preventDefault(); alert('POST only. Use the Print Cards section below.');">
    <div class="text-3xl mb-1">🖨️</div>
    <div class="font-bold text-navy text-sm">Print Cards</div>
    <div class="text-xs text-gray-500">Bulk PDF</div>
  </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

  {{-- STUDENT QR --}}
  @if ($student)
    <div class="bg-white rounded-2xl p-6 shadow text-center">
      <div class="text-xs tracking-widest font-bold text-gold mb-1">STUDENT QR</div>
      <div class="font-extrabold text-navy text-lg mb-1">{{ $student->name }}</div>
      <div class="text-xs text-gray-500 mb-4">{{ $student->adm_no }} · {{ $student->class }}</div>

      <img src="https://quickchart.io/qr?text={{ urlencode(url('/student-qr/' . $student->qr_token)) }}&size=250&margin=1" class="mx-auto mb-4 rounded-xl border-4 border-navy" style="width:250px;height:250px;">

      <div class="text-xs text-gray-500 mb-4 break-all">{{ url('/student-qr/' . $student->qr_token) }}</div>

      <div class="text-xs text-navy bg-navy/5 rounded-lg p-2 mb-3">
        <strong>Used at:</strong> Canteen · Library · Bus · Gate
      </div>

      <a href="{{ url('/student-qr/' . $student->qr_token) }}" target="_blank" class="block text-xs py-2 px-4 rounded-lg bg-navy text-white font-semibold">Open Public View →</a>
    </div>
  @endif

  {{-- RECEIPT QR --}}
  @if ($payment)
    <div class="bg-white rounded-2xl p-6 shadow text-center">
      <div class="text-xs tracking-widest font-bold text-gold mb-1">RECEIPT QR</div>
      <div class="font-extrabold text-navy text-lg mb-1">KES {{ number_format($payment->amount, 0) }}</div>
      <div class="text-xs text-gray-500 mb-4">{{ $payment->receipt_no }}</div>

      <img src="https://quickchart.io/qr?text={{ urlencode(url('/verify-receipt/' . $payment->receipt_no)) }}&size=250&margin=1" class="mx-auto mb-4 rounded-xl border-4 border-green-600" style="width:250px;height:250px;">

      <div class="text-xs text-gray-500 mb-4 break-all">{{ url('/verify-receipt/' . $payment->receipt_no) }}</div>

      <div class="text-xs text-green-700 bg-green-50 rounded-lg p-2 mb-3">
        <strong>Used at:</strong> Receipt verification (kills fake receipts)
      </div>

      <a href="{{ url('/verify-receipt/' . $payment->receipt_no) }}" target="_blank" class="block text-xs py-2 px-4 rounded-lg bg-green-600 text-white font-semibold">Verify Now →</a>
    </div>
  @endif

  {{-- PICKUP QR --}}
  @if ($pickup)
    <div class="bg-white rounded-2xl p-6 shadow text-center">
      <div class="text-xs tracking-widest font-bold text-gold mb-1">PICKUP QR</div>
      <div class="font-extrabold text-navy text-lg mb-1">{{ $pickup->name }}</div>
      <div class="text-xs text-gray-500 mb-4">{{ $pickup->relationship }} · {{ $pickup->student->name ?? '' }}</div>

      <img src="https://quickchart.io/qr?text={{ urlencode(url('/pickup/verify/' . $pickup->qr_token)) }}&size=250&margin=1" class="mx-auto mb-4 rounded-xl border-4 border-red-600" style="width:250px;height:250px;">

      <div class="text-xs text-gray-500 mb-4 break-all">{{ url('/pickup/verify/' . $pickup->qr_token) }}</div>

      <div class="text-xs text-red-700 bg-red-50 rounded-lg p-2 mb-3">
        <strong>Used at:</strong> Guard scanner (child pickup safety)
      </div>

      <a href="{{ url('/pickup/verify/' . $pickup->qr_token) }}" target="_blank" class="block text-xs py-2 px-4 rounded-lg bg-red-600 text-white font-semibold">Test Scan →</a>
    </div>
  @else
    <div class="bg-white rounded-2xl p-6 shadow text-center">
      <div class="text-4xl mb-2">🚫</div>
      <div class="font-bold text-navy">No Active Pickup</div>
      <div class="text-xs text-gray-500 mt-2">Add an approved pickup via Parent Portal first.</div>
      <a href="/parent/login" class="block mt-4 text-xs py-2 px-4 rounded-lg bg-gold text-navy font-semibold">Parent Login →</a>
    </div>
  @endif
</div>

{{-- BULK QR PRINT --}}
<div class="mt-6 bg-white rounded-2xl p-6 shadow">
  <h2 class="font-extrabold text-navy mb-3">🖨️ Bulk Print QR Cards</h2>
  <p class="text-sm text-gray-500 mb-4">Generate a PDF with one QR card per student for a class.</p>
  <form method="POST" action="{{ route('student-qr-cards.bulk-pdf') }}" class="flex flex-wrap gap-2">
    @csrf
    <input name="class" placeholder="e.g. Class 6" required class="flex-1 min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
    <input name="stream" placeholder="Stream (optional)" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
    <button class="px-4 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">📄 Generate PDF</button>
  </form>
</div>

{{-- INSTRUCTIONS --}}
<div class="mt-6 bg-gold/10 border-l-4 border-gold rounded-xl p-5">
  <h3 class="font-bold text-navy mb-2">📸 How to Test QR Scanning</h3>
  <ol class="text-sm text-navy space-y-1 list-decimal list-inside">
    <li><strong>On a phone:</strong> open any URL above in a browser</li>
    <li><strong>On the same or another phone:</strong> open <code>/security/login</code> (or <code>/canteen</code>)</li>
    <li>Login → tap <strong>Start Camera</strong> → point camera at any QR</li>
    <li>Auto-verifies in 1 second ✅ or ⛔</li>
  </ol>
  <p class="text-xs text-navy mt-2">
    <strong>Camera needs HTTPS</strong> — use <code>https://{{ request()->getHost() }}</code> (works via cloudflared tunnel).
  </p>
</div>
@endsection
