@extends('layouts.admin')
@section('title', 'SMS Center')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">SMS Center</h1>
  <p class="text-sm text-gray-500 mt-1">Broadcast messages to parents, teachers, or defaulters.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

{{-- STATS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-semibold">PARENTS</div>
    <div class="text-2xl font-extrabold text-navy mt-1">{{ $parentCount }}</div>
    <div class="text-xs text-gray-500 mt-1">Unique phone numbers</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-semibold">TEACHERS</div>
    <div class="text-2xl font-extrabold text-navy mt-1">{{ $teacherCount }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-semibold text-red-600">DEFAULTERS</div>
    <div class="text-2xl font-extrabold text-red-600 mt-1">{{ $defaulterCount }}</div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  {{-- COMPOSE --}}
  <div class="bg-white rounded-2xl p-6 md:p-8 shadow">
    <h2 class="font-extrabold text-navy mb-4">📤 Compose SMS</h2>

    <form method="POST" action="{{ route('principal.sms.send') }}" class="space-y-4" id="smsForm">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AUDIENCE *</label>
        <select name="audience" id="audience" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="parents">👨‍👩‍👧 All Parents ({{ $parentCount }})</option>
          <option value="teachers">👩‍🏫 All Teachers ({{ $teacherCount }})</option>
          <option value="defaulters">⚠️ Fee Defaulters ({{ $defaulterCount }})</option>
          <option value="custom">✏️ Custom Numbers</option>
        </select>
      </div>

      <div id="customPhones" class="hidden">
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CUSTOM PHONES (comma-separated)</label>
        <textarea name="custom_phones" rows="2" placeholder="0712345678,0723456789" class="w-full rounded-xl border-2 border-gray-200 px-4 py-2 text-sm focus:border-gold focus:outline-none"></textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">MESSAGE *</label>
        <textarea name="message" id="msg" rows="5" required maxlength="480" placeholder="Dear parent, ..."
                  class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:border-gold focus:outline-none"></textarea>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>Max 480 characters (3 SMS segments)</span>
          <span id="charCount">0 / 480</span>
        </div>
      </div>

      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">📱 Send SMS</button>
    </form>
  </div>

  {{-- RECENT --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📋 Recent Broadcasts (this session)</h2>

    @if (empty($recent))
      <div class="text-center py-10 text-gray-400 text-sm">
        <div class="text-4xl mb-2">📭</div>
        No broadcasts yet this session
      </div>
    @else
      <div class="space-y-3">
        @foreach ($recent as $r)
          <div class="p-3 rounded-xl bg-gray-50 border-l-4 border-navy">
            <div class="flex justify-between text-xs text-gray-500">
              <span>{{ $r['time'] }}</span>
              <span class="capitalize font-semibold text-navy">{{ $r['audience'] }}</span>
            </div>
            <div class="text-sm text-gray-700 mt-1">{{ $r['preview'] }}...</div>
            <div class="text-xs mt-1">
              <span class="text-green-600 font-bold">✓ {{ $r['sent'] }}</span>
              <span class="text-red-600 font-bold ml-2">✗ {{ $r['failed'] }}</span>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>

@push('scripts')
<script>
  const audience = document.getElementById('audience');
  const custom   = document.getElementById('customPhones');
  const msg      = document.getElementById('msg');
  const count    = document.getElementById('charCount');

  audience?.addEventListener('change', () => {
    custom.classList.toggle('hidden', audience.value !== 'custom');
  });

  msg?.addEventListener('input', () => {
    count.textContent = msg.value.length + ' / 480';
  });
</script>
@endpush

@endsection
