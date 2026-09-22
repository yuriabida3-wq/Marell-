@extends('layouts.admin')
@section('title', 'Fee Timeline — ' . $student->name)
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.fee-autopilot.index') }}" class="text-xs text-gray-500">← Fee Autopilot</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $student->name }}</h1>
  <p class="text-sm text-gray-500">{{ $student->adm_no }} · {{ $student->class }} · Parent: {{ $student->parent_name }}</p>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">BALANCE</div>
    <div class="text-2xl font-extrabold text-red-600 mt-1">KES {{ number_format((float) $student->balance, 0) }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">ESCALATIONS SENT</div>
    <div class="text-2xl font-extrabold text-navy mt-1">{{ $escalations->count() }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">LAST CONTACT</div>
    <div class="text-lg font-extrabold text-navy mt-1">{{ $escalations->last()?->created_at->format('d M H:i') ?? 'Never' }}</div>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="flex flex-wrap gap-2 mb-4">
  <form method="POST" action="{{ route('principal.fee-autopilot.trigger', $student) }}" class="inline">
    @csrf
    <button class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm">⚡ Trigger Next Stage</button>
  </form>
  <a href="tel:{{ $student->parent_phone }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">📞 Call Parent</a>
  <a href="https://wa.me/{{ preg_replace('/\D/','',$student->parent_phone) }}" target="_blank" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold">💬 WhatsApp</a>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy">📋 Escalation Timeline</div>
  </div>
  <div class="p-5">
    @forelse ($escalations->sortBy('stage') as $e)
      <div class="flex gap-4 pb-6 border-l-2 border-navy/30 relative pl-6 {{ $loop->last ? '' : 'mb-2' }}">
        <div class="absolute -left-3 top-0 w-6 h-6 rounded-full bg-navy text-white flex items-center justify-center text-xs font-bold">{{ $e->stage }}</div>
        <div class="flex-1">
          <div class="flex justify-between items-start mb-1">
            <div class="font-bold text-navy text-sm">Stage {{ $e->stage }} — {{ $e->title }}</div>
            <div class="text-xs text-gray-500">{{ $e->created_at->format('d M Y H:i') }}</div>
          </div>
          <div class="text-xs text-gray-600 bg-gray-50 rounded-lg p-3">{{ $e->message }}</div>
          <div class="flex gap-3 mt-2 text-xs">
            <span class="font-bold">{{ strtoupper($e->channel) }}</span>
            <span class="px-2 py-0.5 rounded-full font-bold {{ $e->statusColor() }}">{{ strtoupper($e->status) }}</span>
            @if ($e->sent_at)<span class="text-green-600">Sent {{ $e->sent_at->format('H:i') }}</span>@endif
            @if ($e->error)<span class="text-red-600">Error: {{ \Illuminate\Support\Str::limit($e->error, 50) }}</span>@endif
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-8 text-gray-400">No escalations yet for this student.</div>
    @endforelse
  </div>
</div>
@endsection
