@extends('layouts.admin')
@section('title', 'Alert')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.emergency.index') }}" class="text-xs text-gray-500">← Alerts</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $alert->title }}</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">STATUS</div>
    <div class="text-xl font-extrabold text-navy mt-1">{{ strtoupper($alert->status) }}</div>
  </div>
  <div class="bg-green-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">SENT</div>
    <div class="text-xl font-extrabold text-green-700 mt-1">{{ $alert->sent_count }} / {{ $alert->total_recipients }}</div>
  </div>
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">FAILED</div>
    <div class="text-xl font-extrabold text-red-600 mt-1">{{ $alert->failed_count }}</div>
  </div>
</div>

<div class="bg-white rounded-2xl p-6 shadow">
  <div class="flex items-center justify-between mb-4">
    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $alert->severityColor() }}">{{ strtoupper($alert->severity) }}</span>
    <span class="text-xs text-gray-500">Audience: <strong>{{ $alert->audience }}</strong></span>
  </div>
  <div class="bg-gray-50 rounded-xl p-4 whitespace-pre-wrap text-sm">{{ $alert->message }}</div>
  <div class="mt-4 grid grid-cols-2 text-xs text-gray-500">
    <div>Sent by: {{ $alert->sender->name ?? 'System' }}</div>
    <div class="text-right">Sent at: {{ $alert->sent_at?->format('d M Y H:i') ?? '—' }}</div>
  </div>
</div>

<div class="mt-4 text-right">
  <form method="POST" action="{{ route('principal.emergency.destroy', $alert) }}" onsubmit="return confirm('Delete this alert log?')">
    @csrf @method('DELETE')
    <button class="text-xs text-red-600 hover:underline">Delete alert log</button>
  </form>
</div>
@endsection
