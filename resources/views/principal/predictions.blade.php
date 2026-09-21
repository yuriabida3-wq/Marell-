@extends('layouts.admin')
@section('title', 'AI Predictions')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">AI Defaulter Predictions</h1>
  <p class="text-sm text-gray-500 mt-1">Based on payment patterns, balance ratios, and family history</p>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-red-700">HIGH RISK</div>
    <div class="text-2xl font-extrabold text-red-600 mt-1">{{ $redCount }}</div>
    <div class="text-xs text-gray-500 mt-1">Likely to default</div>
  </div>
  <div class="bg-yellow-50 rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-yellow-700">WATCH LIST</div>
    <div class="text-2xl font-extrabold text-yellow-600 mt-1">{{ $yellowCount }}</div>
    <div class="text-xs text-gray-500 mt-1">Show signs of risk</div>
  </div>
  <div class="bg-navy rounded-2xl p-5 shadow text-white">
    <div class="text-[10px] tracking-widest font-bold text-gold">AT-RISK BALANCE</div>
    <div class="text-2xl font-extrabold mt-1">KES {{ number_format($totalAtRisk, 0) }}</div>
    <div class="text-xs text-white/70 mt-1">Could be lost if not addressed</div>
  </div>
</div>

<div class="bg-gold/10 border-l-4 border-gold rounded-xl p-4 mb-6 text-sm text-navy">
  <strong>Pro tip:</strong> Call the top 5 parents today. Sending an SMS reminder 3 days before due date cuts defaulters by 60%.
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">RISK</th>
        <th class="p-3">STUDENT</th>
        <th class="p-3">CLASS</th>
        <th class="p-3 text-right">BALANCE</th>
        <th class="p-3">WHY FLAGGED</th>
        <th class="p-3 text-right">ACTION</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($predictions as $p)
        @php
          $level = \App\Services\DefaulterPredictionService::riskLevel($p['score']);
          $badge = match($level) {
            'red'    => 'bg-red-100 text-red-700 border-red-300',
            'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            default  => 'bg-green-100 text-green-700 border-green-300',
          };
        @endphp
        <tr class="hover:bg-gray-50">
          <td class="p-3">
            <div class="px-3 py-1 rounded-full border-2 {{ $badge }} font-bold text-xs text-center inline-block">
              {{ $p['score'] }}%
            </div>
          </td>
          <td class="p-3">
            <div class="font-semibold text-navy">{{ $p['student']->name }}</div>
            <div class="text-xs text-gray-500 font-mono">{{ $p['student']->adm_no }}</div>
          </td>
          <td class="p-3 text-gray-600">{{ $p['student']->class }} {{ $p['student']->stream }}</td>
          <td class="p-3 text-right font-bold text-red-600">KES {{ number_format($p['student']->balance, 0) }}</td>
          <td class="p-3 text-xs text-gray-600">
            @foreach ($p['reasons'] as $r)
              <div>• {{ $r }}</div>
            @endforeach
          </td>
          <td class="p-3 text-right whitespace-nowrap">
            <a href="tel:{{ $p['student']->parent_phone }}" class="text-xs font-semibold text-navy hover:text-gold">📞 Call</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-12 text-center text-gray-400">No high-risk students found. Great job!</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-6 text-xs text-gray-500 text-center">
  Predictions refresh on every page load using live data.
</div>

@endsection
