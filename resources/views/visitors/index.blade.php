@extends('layouts.admin')
@section('title', 'Visitor Logs')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <a href="{{ route('visitors.gate') }}" class="text-xs text-gray-500">← Gate</a>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">📋 Visitor Logs</h1>
    <p class="text-sm text-gray-500">{{ $visitors->total() }} records</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('visitors.check-in') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm">+ Check In</a>
    <a href="{{ route('visitors.pre-register') }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">⏳ Pre-Register</a>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-2">
    <input name="q" value="{{ $q }}" placeholder="🔍 Name, phone, badge, plate" class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
    <select name="status" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">All Statuses</option>
      <option value="expected"    @selected($status === 'expected')>Expected</option>
      <option value="checked_in"  @selected($status === 'checked_in')>Inside</option>
      <option value="checked_out" @selected($status === 'checked_out')>Checked Out</option>
    </select>
    <button class="min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Filter</button>
    <input type="date" name="from" value="{{ $from }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-xs">
    <input type="date" name="to" value="{{ $to }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-xs md:col-span-2">
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">BADGE</th>
          <th class="p-3">NAME</th>
          <th class="p-3">PURPOSE</th>
          <th class="p-3">IN</th>
          <th class="p-3">OUT</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($visitors as $v)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-mono text-xs font-bold text-navy">{{ $v->badge_no ?? '—' }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy">{{ $v->name }}</div>
              <div class="text-xs text-gray-500 font-mono">{{ $v->phone }}</div>
            </td>
            <td class="p-3 text-xs text-gray-700">{{ \Illuminate\Support\Str::limit($v->purpose, 40) }}</td>
            <td class="p-3 text-xs">{{ $v->checked_in_at?->format('d M H:i') ?? '—' }}</td>
            <td class="p-3 text-xs">{{ $v->checked_out_at?->format('d M H:i') ?? '—' }}</td>
            <td class="p-3 text-center">
              @php $sc = match($v->status) { 'checked_in' => 'bg-green-100 text-green-700', 'expected' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
              <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $sc }}">{{ str_replace('_',' ', strtoupper($v->status)) }}</span>
            </td>
            <td class="p-3 text-right">
              <a href="{{ route('visitors.show', $v) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="p-12 text-center text-gray-400">No visitor records</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $visitors->links() }}</div>
@endsection
