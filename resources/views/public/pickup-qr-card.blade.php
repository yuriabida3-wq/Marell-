@extends('layouts.public')
@section('title', 'Pickup QR — ' . $pickup->name)
@section('content')

<section class="bg-navy text-white py-8">
  <div class="max-w-2xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-xs">APPROVED PICKUP QR</div>
    <h1 class="text-2xl md:text-3xl font-extrabold mt-1">{{ $pickup->name }}</h1>
  </div>
</section>

<section class="max-w-2xl mx-auto px-4 py-8">
  <div class="card p-6 md:p-8 text-center">

    @if (!$pickup->active)
      <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded mb-4 text-sm text-red-700 text-left">
        ⚠️ This pickup pass is <strong>INACTIVE</strong>. The person cannot collect your child. Activate it from the dashboard.
      </div>
    @endif

    <div class="bg-gold/10 border border-gold rounded-xl p-4 mb-6">
      <div class="text-xs text-gray-500 tracking-widest font-bold">STUDENT</div>
      <div class="font-bold text-navy text-lg mt-1">{{ $pickup->student->name }}</div>
      <div class="text-xs text-gray-500">{{ $pickup->student->adm_no }} · {{ $pickup->student->class }}</div>
    </div>

    {{-- QR CODE --}}
    <div class="bg-white border-4 border-navy rounded-2xl p-6 inline-block mb-6">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={{ urlencode($pickup->qrUrl()) }}"
           alt="Pickup QR" class="w-64 h-64 mx-auto">
    </div>

    <div class="bg-navy/5 rounded-xl p-4 text-sm text-navy">
      <strong>How to use:</strong><br>
      Show this QR to the security guard at the gate.<br>
      Guard scans → system verifies → child released or denied.
    </div>

    <div class="mt-6 grid grid-cols-3 gap-3 text-left text-xs">
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-gray-500">Relationship</div>
        <div class="font-bold text-navy">{{ $pickup->relationship }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-gray-500">Phone</div>
        <div class="font-bold text-navy text-xs">{{ $pickup->phone }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-gray-500">Status</div>
        <div class="font-bold {{ $pickup->active ? 'text-green-600' : 'text-red-600' }}">{{ $pickup->active ? 'Active' : 'Inactive' }}</div>
      </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-2 justify-center">
      <a href="{{ route('parent.pickups', ['child' => $pickup->student_id]) }}" class="btn bg-gray-200 text-navy text-sm">← Back</a>
      <a href="https://wa.me/?text={{ urlencode($pickup->name . ' is approved to pick up ' . $pickup->student->name . '. QR: ' . $pickup->qrUrl()) }}"
         target="_blank" class="btn bg-green-500 text-white text-sm">💬 Share on WhatsApp</a>
    </div>
  </div>
</section>
@endsection
