@extends('layouts.admin')
@section('title', $student->name)
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <a href="{{ route('principal.students.index') }}" class="text-xs text-gray-500 hover:text-navy">← All Students</a>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $student->name }}</h1>
    <p class="text-sm text-gray-500">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('principal.students.edit', $student) }}" class="px-4 py-2 rounded-xl bg-navy text-white font-semibold text-sm hover:scale-105 transition">✏️ Edit</a>
    <form method="POST" action="{{ route('principal.students.toggle', $student) }}">
      @csrf
      <button class="px-4 py-2 rounded-xl {{ $student->status === 'active' ? 'bg-red-600' : 'bg-green-600' }} text-white font-semibold text-sm hover:scale-105 transition">
        {{ $student->status === 'active' ? '🚫 Suspend' : '✅ Activate' }}
      </button>
    </form>
  </div>
</div>

{{-- FEE SUMMARY --}}
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-gold/20 rounded-2xl p-5 shadow">
    <div class="text-xs text-navy tracking-widest font-semibold">SIBLING DISCOUNT</div>
    <div class="text-2xl font-extrabold text-navy mt-1">KES {{ number_format($student->discount_amount, 0) }}</div>
    <div class="text-xs text-gray-600 mt-1">Child #{{ $student->sibling_order }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-semibold">TOTAL FEE</div>
    <div class="text-2xl font-extrabold text-navy mt-1">KES {{ number_format($student->total_fee, 0) }}</div>
  </div>
  <div class="bg-green-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-semibold">PAID</div>
    <div class="text-2xl font-extrabold text-green-700 mt-1">KES {{ number_format($student->paid_amount, 0) }}</div>
  </div>
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest font-semibold">BALANCE</div>
    <div class="text-2xl font-extrabold text-red-600 mt-1">KES {{ number_format($student->balance, 0) }}</div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  {{-- PROFILE --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">👤 Profile</h2>
    <table class="w-full text-sm">
      <tr class="border-b"><td class="py-2 text-gray-500">ADM No</td><td class="py-2 font-semibold text-navy text-right">{{ $student->adm_no }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Class</td><td class="py-2 text-right">{{ $student->class }} {{ $student->stream }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Parent</td><td class="py-2 text-right">{{ $student->parent_name }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Phone</td><td class="py-2 text-right font-mono text-xs">{{ $student->parent_phone }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Email</td><td class="py-2 text-right">{{ $student->parent_email ?: '—' }}</td></tr>
      <tr><td class="py-2 text-gray-500">Status</td><td class="py-2 text-right font-semibold">{{ ucfirst($student->status) }}</td></tr>
    </table>
  </div>

  {{-- RESULTS --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-extrabold text-navy">📊 Results</h2>
      <span class="text-xs text-gray-500">{{ $results->count() }} subjects</span>
    </div>
    @if ($results->count())
      <table class="w-full text-sm">
        <thead class="text-xs text-gray-500">
          <tr><th class="text-left py-1">Subject</th><th class="text-center">Marks</th><th class="text-center">Grade</th></tr>
        </thead>
        <tbody>
          @foreach ($results as $r)
            <tr class="border-t">
              <td class="py-2">{{ $r->subject }}</td>
              <td class="py-2 text-center font-bold">{{ $r->marks }}</td>
              <td class="py-2 text-center font-bold text-navy">{{ $r->grade }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="text-sm text-gray-400 text-center py-6">No results yet</p>
    @endif
  </div>
</div>

{{-- PAYMENTS --}}
<div class="bg-white rounded-2xl shadow mt-6 overflow-hidden">
  <div class="p-5 border-b flex items-center justify-between">
    <h2 class="font-extrabold text-navy">💳 Payment History</h2>
    <span class="text-xs text-gray-500">{{ $payments->count() }} records</span>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">DATE</th>
          <th class="p-3">METHOD</th>
          <th class="p-3">TXN</th>
          <th class="p-3 text-right">AMOUNT</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">RECEIPT</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($payments as $p)
          <tr>
            <td class="p-3 text-gray-500">{{ $p->created_at->format('d M Y H:i') }}</td>
            <td class="p-3">{{ $p->method }}</td>
            <td class="p-3 font-mono text-xs">{{ $p->transaction_code ?: '—' }}</td>
            <td class="p-3 text-right font-bold text-navy">KES {{ number_format($p->amount, 0) }}</td>
            <td class="p-3 text-center">
              <span class="px-2 py-1 rounded-full text-xs font-bold
                @if($p->status==='completed') bg-green-100 text-green-700
                @elseif($p->status==='pending') bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700 @endif">{{ ucfirst($p->status) }}</span>
            </td>
            <td class="p-3 text-right">
              @if ($p->status === 'completed')
                <a href="{{ URL::signedRoute('pay.receipt', ['payment' => $p->id]) }}" class="text-xs font-semibold text-navy hover:text-gold">📄 PDF</a>
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-gray-400 text-sm">No payments yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
