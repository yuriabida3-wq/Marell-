@extends('layouts.admin')
@section('title', 'Visitor Details')
@section('content')

<div class="mb-6">
  <a href="{{ route('visitors.index') }}" class="text-xs text-gray-500">← Logs</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $visitor->name }}</h1>
  @if ($visitor->badge_no)
    <div class="inline-block mt-2 px-3 py-1 rounded-full bg-gold text-navy font-bold text-sm">Badge: {{ $visitor->badge_no }}</div>
  @endif
</div>

<div class="bg-white rounded-2xl p-6 shadow max-w-3xl">
  <table class="w-full text-sm">
    <tr class="border-b"><td class="py-2 text-gray-500 w-1/3">Phone</td><td class="py-2 font-mono text-right">{{ $visitor->phone }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">ID Number</td><td class="py-2 text-right">{{ $visitor->id_number ?? '—' }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Purpose</td><td class="py-2 text-right">{{ $visitor->purpose }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Host</td><td class="py-2 text-right">{{ $visitor->host_name ?? '—' }} <span class="text-xs text-gray-400">({{ $visitor->host_type ?? '—' }})</span></td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Student ADM</td><td class="py-2 text-right font-mono">{{ $visitor->student_adm ?? '—' }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Vehicle</td><td class="py-2 text-right font-mono">{{ $visitor->vehicle_plate ?? '—' }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Status</td><td class="py-2 text-right font-bold text-navy">{{ strtoupper(str_replace('_',' ',$visitor->status)) }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Checked In</td><td class="py-2 text-right">{{ $visitor->checked_in_at?->format('d M Y H:i') ?? '—' }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Checked Out</td><td class="py-2 text-right">{{ $visitor->checked_out_at?->format('d M Y H:i') ?? '—' }}</td></tr>
    @if ($visitor->notes)
      <tr><td class="py-2 text-gray-500 align-top">Notes</td><td class="py-2 text-right">{{ $visitor->notes }}</td></tr>
    @endif
  </table>

  @if ($visitor->status === 'checked_in')
    <form method="POST" action="{{ route('visitors.check-out', $visitor) }}" class="mt-6">
      @csrf
      <button class="w-full min-h-[48px] rounded-xl bg-red-600 text-white font-bold">Check Out</button>
    </form>
  @endif
</div>
@endsection
