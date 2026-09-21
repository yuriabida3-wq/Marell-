@extends('layouts.admin')
@section('title', 'Emergency Alerts')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">🚨 Emergency Broadcast</h1>
    <p class="text-sm text-gray-500 mt-1">One-tap SMS to all parents / teachers. Use for real emergencies.</p>
  </div>
  <a href="{{ route('principal.emergency.create') }}" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-sm hover:scale-105 transition">+ New Alert</a>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="grid grid-cols-2 gap-4 mb-6">
  <div class="bg-navy rounded-2xl p-5 text-white">
    <div class="text-[10px] tracking-widest font-bold text-gold">PARENTS REACHABLE</div>
    <div class="text-3xl font-extrabold mt-1">{{ number_format($parentCount) }}</div>
    <div class="text-xs text-white/70 mt-1">Unique phone numbers</div>
  </div>
  <div class="bg-gold rounded-2xl p-5 text-navy">
    <div class="text-[10px] tracking-widest font-bold">TEACHERS REACHABLE</div>
    <div class="text-3xl font-extrabold mt-1">{{ number_format($teacherCount) }}</div>
    <div class="text-xs text-navy/70 mt-1">On payroll</div>
  </div>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">WHEN</th>
        <th class="p-3">TITLE</th>
        <th class="p-3">SEVERITY</th>
        <th class="p-3">AUDIENCE</th>
        <th class="p-3 text-center">DELIVERY</th>
        <th class="p-3 text-center">STATUS</th>
        <th class="p-3 text-right">ACTION</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($alerts as $a)
        <tr class="hover:bg-gray-50">
          <td class="p-3 text-xs text-gray-500">{{ $a->created_at->format('d M H:i') }}</td>
          <td class="p-3 font-semibold text-navy">{{ $a->title }}</td>
          <td class="p-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-bold border {{ $a->severityColor() }}">
              {{ strtoupper($a->severity) }}
            </span>
          </td>
          <td class="p-3 text-gray-600 capitalize">{{ $a->audience }}</td>
          <td class="p-3 text-center text-xs">
            <span class="text-green-600 font-bold">{{ $a->sent_count }}</span> /
            <span class="text-red-600 font-bold">{{ $a->failed_count }}</span>
          </td>
          <td class="p-3 text-center">
            @php $sc = match($a->status) { 'sent' => 'bg-green-100 text-green-700', 'sending' => 'bg-blue-100 text-blue-700', 'failed' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' }; @endphp
            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $sc }}">{{ strtoupper($a->status) }}</span>
          </td>
          <td class="p-3 text-right">
            <a href="{{ route('principal.emergency.show', $a) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="p-12 text-center text-gray-400">No alerts sent yet</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $alerts->links() }}</div>
@endsection
