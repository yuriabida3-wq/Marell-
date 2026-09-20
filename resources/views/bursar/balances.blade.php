@extends('layouts.bursar')
@section('title', 'Fee Balances')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Fee Balances</h1>
    <p class="text-sm text-gray-500 mt-1">Total owed: <span class="font-bold text-red-600">KES {{ number_format($totalOwed, 0) }}</span></p>
  </div>
  <a href="{{ route('bursar.balances.export') }}?{{ http_build_query(request()->only(['class','defaulters'])) }}"
     class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm">📊 Export CSV</a>
</div>

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-2">
    <select name="class" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">All Classes</option>
      @foreach ($classes as $c)
        <option value="{{ $c }}" @selected($class===$c)>{{ $c }}</option>
      @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="defaulters" value="1" @checked($onlyDefaulters) class="w-5 h-5"> Only with balance</label>
    <button class="min-h-[42px] rounded-xl bg-navy text-white text-sm font-semibold">Apply</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs md:text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">ADM</th>
          <th class="p-3">NAME</th>
          <th class="p-3">CLASS</th>
          <th class="p-3 text-right">PAID</th>
          <th class="p-3 text-right">BALANCE</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($students as $s)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-mono text-navy text-xs">{{ $s->adm_no }}</td>
            <td class="p-3 font-semibold text-navy">{{ $s->name }}</td>
            <td class="p-3 text-gray-600">{{ $s->class }}</td>
            <td class="p-3 text-right text-green-700">KES {{ number_format($s->paid_amount, 0) }}</td>
            <td class="p-3 text-right font-bold {{ $s->balance > 0 ? 'text-red-600' : 'text-green-600' }}">KES {{ number_format($s->balance, 0) }}</td>
            <td class="p-3 text-right"><a href="{{ route('bursar.students') }}?adm={{ $s->adm_no }}" class="text-navy hover:text-gold font-semibold text-xs">Open</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-12 text-center text-gray-400">No students</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $students->links() }}</div>

@endsection
