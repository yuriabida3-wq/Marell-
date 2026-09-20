@extends('layouts.admin')
@section('title', 'Defaulters')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Fee Defaulters</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $defaulters->total() }} students · Total owed: <span class="font-bold text-red-600">KES {{ number_format($totalOwed, 0) }}</span></p>
  </div>
  <a href="{{ route('principal.finance.export-defaulters') }}?{{ http_build_query(request()->only(['min_balance','class'])) }}"
     class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:scale-105 transition">📊 Export CSV</a>
</div>

{{-- FILTERS --}}
<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <select name="class" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <option value="">All Classes</option>
      @foreach ($classes as $c)
        <option value="{{ $c }}" @selected($class === $c)>{{ $c }}</option>
      @endforeach
    </select>
    <input name="min_balance" type="number" value="{{ $minBalance }}" placeholder="Min balance"
           class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
    <button class="min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm hover:scale-[1.02] transition">Apply</button>
  </form>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-red-50 text-xs text-red-700">
        <tr class="text-left">
          <th class="p-3">ADM</th>
          <th class="p-3">NAME</th>
          <th class="p-3">CLASS</th>
          <th class="p-3">PARENT</th>
          <th class="p-3">PHONE</th>
          <th class="p-3 text-right">PAID</th>
          <th class="p-3 text-right">BALANCE</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($defaulters as $s)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-mono text-xs text-navy">{{ $s->adm_no }}</td>
            <td class="p-3 font-semibold text-navy">{{ $s->name }}</td>
            <td class="p-3 text-gray-600">{{ $s->class }}</td>
            <td class="p-3 text-gray-600">{{ $s->parent_name }}</td>
            <td class="p-3 text-gray-600 font-mono text-xs">{{ $s->parent_phone }}</td>
            <td class="p-3 text-right text-green-700">KES {{ number_format($s->paid_amount, 0) }}</td>
            <td class="p-3 text-right font-bold text-red-600">KES {{ number_format($s->balance, 0) }}</td>
            <td class="p-3 text-right">
              <a href="{{ route('principal.students.show', $s) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-12 text-center text-gray-400 text-sm">🎉 No defaulters match the filter</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $defaulters->links() }}</div>

@endsection
