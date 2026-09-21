@extends('layouts.admin')
@section('title', 'New Emergency Alert')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.emergency.index') }}" class="text-xs text-gray-500">← Alerts</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">🚨 New Emergency Alert</h1>
</div>

@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4">
    <ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
  </div>
@endif

<div class="bg-red-50 border-2 border-red-300 rounded-2xl p-4 mb-6">
  <div class="font-bold text-red-700 mb-1">⚠️ This sends real SMS to every recipient instantly.</div>
  <div class="text-sm text-red-600">Costs apply (Africa's Talking). Only use for genuine emergencies.</div>
</div>

<div class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <form method="POST" action="{{ route('principal.emergency.store') }}" class="space-y-4" onsubmit="return confirm('Send this alert NOW to all selected recipients?')">
    @csrf

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SEVERITY *</label>
      <div class="grid grid-cols-3 gap-2">
        @foreach (['info' => ['ℹ️', 'Info', 'bg-blue-50 border-blue-300 text-blue-700'], 'warning' => ['⚠️', 'Warning', 'bg-yellow-50 border-yellow-300 text-yellow-700'], 'critical' => ['🚨', 'Critical', 'bg-red-50 border-red-300 text-red-700']] as $key => $opt)
          <label class="cursor-pointer">
            <input type="radio" name="severity" value="{{ $key }}" class="peer sr-only" @checked(old('severity', 'info') === $key)>
            <div class="border-2 rounded-xl p-3 text-center font-semibold text-sm transition {{ $opt[2] }} peer-checked:ring-4 peer-checked:ring-navy/30">
              <div class="text-2xl mb-1">{{ $opt[0] }}</div>
              {{ $opt[1] }}
            </div>
          </label>
        @endforeach
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">AUDIENCE *</label>
      <select name="audience" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <option value="parents"  @selected(old('audience') === 'parents')>Parents only</option>
        <option value="teachers" @selected(old('audience') === 'teachers')>Teachers only</option>
        <option value="both"     @selected(old('audience', 'both') === 'both')>Both parents + teachers</option>
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TITLE * (short — appears first)</label>
      <input name="title" value="{{ old('title') }}" required maxlength="120" placeholder="e.g. School closed tomorrow" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
    </div>

    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">MESSAGE * (max 1000 chars)</label>
      <textarea name="message" rows="5" required maxlength="1000" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3" placeholder="Full details...">{{ old('message') }}</textarea>
      <div class="text-xs text-gray-500 mt-1">Keep it short — SMS charges apply per 160 characters.</div>
    </div>

    <button class="w-full min-h-[52px] rounded-xl bg-red-600 text-white font-bold hover:scale-[1.02] transition">🚨 SEND TO EVERYONE NOW</button>
  </form>
</div>
@endsection
