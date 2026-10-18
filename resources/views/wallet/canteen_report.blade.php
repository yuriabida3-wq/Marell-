@extends('layouts.admin')
@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Canteen Sales Report</h1>
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow"><p class="text-sm text-gray-500">Total Revenue ({{ $days }}d)</p><p class="text-2xl font-bold text-navy">KES {{ number_format($total, 2) }}</p></div>
        <div class="bg-white p-4 rounded-xl shadow"><p class="text-sm text-gray-500">Transactions</p><p class="text-2xl font-bold text-navy">{{ number_format($count) }}</p></div>
        <div class="bg-white p-4 rounded-xl shadow"><p class="text-sm text-gray-500">Average Sale</p><p class="text-2xl font-bold text-navy">KES {{ number_format($avg, 2) }}</p></div>
    </div>
    <div class="bg-white rounded-xl shadow mb-6">
        <h2 class="p-4 font-bold border-b">Daily Breakdown</h2>
        <table class="w-full text-left">
            <thead class="bg-gray-50"><tr><th class="p-3">Date</th><th class="p-3">Transactions</th><th class="p-3">Revenue</th></tr></thead>
            <tbody>
                @forelse($daily as $d)
                <tr class="border-b"><td class="p-3">{{ $d->day }}</td><td class="p-3">{{ $d->txns }}</td><td class="p-3 font-semibold">KES {{ number_format($d->total, 2) }}</td></tr>
                @empty
                <tr><td colspan="3" class="p-3 text-center text-gray-400">No sales in this period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-xl shadow">
        <h2 class="p-4 font-bold border-b">Top Spenders</h2>
        <table class="w-full text-left">
            <thead class="bg-gray-50"><tr><th class="p-3">Student</th><th class="p-3">Transactions</th><th class="p-3">Total Spent</th></tr></thead>
            <tbody>
                @forelse($topSpenders as $s)
                <tr class="border-b"><td class="p-3">{{ $s->student->name ?? "Unknown" }}</td><td class="p-3">{{ $s->txns }}</td><td class="p-3 font-semibold">KES {{ number_format($s->total, 2) }}</td></tr>
                @empty
                <tr><td colspan="3" class="p-3 text-center text-gray-400">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
