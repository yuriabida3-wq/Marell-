@extends('layouts.admin')
@section('title', 'Autopilot Settings')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.fee-autopilot.index') }}" class="text-xs text-gray-500">← Fee Autopilot</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">⚙️ Autopilot Settings</h1>
  <p class="text-sm text-gray-500">Customize each reminder stage. Use {student}, {balance}, {due_date}, {parent}, {adm_no} as placeholders.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<form method="POST" action="{{ route('principal.fee-autopilot.settings.update') }}">
  @csrf
  <div class="space-y-4">
    @foreach ($stages as $s)
      <div class="bg-white rounded-2xl p-5 shadow">
        <div class="flex items-center justify-between mb-3">
          <div>
            <div class="font-bold text-navy">Stage {{ $s->stage }} — {{ $s->name }}</div>
            <div class="text-xs text-gray-500">
              {{ strtoupper($s->channel) }} ·
              @if ($s->days_offset < 0) {{ abs($s->days_offset) }} days before due
              @elseif ($s->days_offset === 0) Due day
              @else {{ $s->days_offset }} days after
              @endif
            </div>
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="active[{{ $s->id }}]" value="1" {{ $s->active ? 'checked' : '' }} class="w-5 h-5">
            Active
          </label>
        </div>
        <textarea name="template[{{ $s->id }}]" rows="2" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm">{{ $s->template }}</textarea>
      </div>
    @endforeach
  </div>

  <button class="mt-6 w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold shadow-lg sticky bottom-4">💾 Save Settings</button>
</form>
@endsection
