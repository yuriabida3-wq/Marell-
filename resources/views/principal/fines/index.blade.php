@extends('layouts.admin')
@section('title', 'Late Fines')
@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Late Payment Fines</h1>
    <p class="text-sm text-gray-500 mt-1">KES 200 auto-applied for balances after 10th of month</p>
  </div>
  <form method="POST" action="{{ route('principal.fines.apply') }}" onsubmit="return confirm('Apply KES 200 fine to all students with balance?')">
    @csrf
    <button class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-sm hover:scale-105 transition">Apply Fines Now</button>
  </form>
</div>

@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>@endif

<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-red-50 rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest font-bold text-red-700">ACTIVE FINES</div><div class="text-2xl font-extrabold text-red-600 mt-1">KES {{ number_format($totalActive, 0) }}</div></div>
  <div class="bg-white rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest font-bold text-gray-500">STUDENTS FINED</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $count }}</div></div>
  <div class="bg-gray-50 rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest font-bold text-gray-500">TOTAL WAIVED</div><div class="text-2xl font-extrabold text-gray-500 mt-1">KES {{ number_format($totalWaived, 0) }}</div></div>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500"><tr class="text-left"><th class="p-3">DATE</th><th class="p-3">STUDENT</th><th class="p-3">ADM</th><th class="p-3 text-right">AMOUNT</th><th class="p-3 text-center">STATUS</th><th class="p-3 text-right">ACTION</th></tr></thead>
    <tbody class="divide-y">
      @forelse ($fines as $f)
        <tr class="hover:bg-gray-50">
          <td class="p-3 text-xs text-gray-500">{{ $f->applied_date->format('d M') }}</td>
          <td class="p-3 font-semibold text-navy">{{ $f->student->name ?? '—' }}</td>
          <td class="p-3 font-mono text-xs">{{ $f->student->adm_no ?? '—' }}</td>
          <td class="p-3 text-right font-bold {{ $f->waived ? 'text-gray-400 line-through' : 'text-red-600' }}">KES {{ number_format($f->amount, 0) }}</td>
          <td class="p-3 text-center">
            @if($f->waived)<span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Waived</span>
            @else<span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Active</span>@endif
          </td>
          <td class="p-3 text-right">
            @if(!$f->waived)
              <form method="POST" action="{{ route('principal.fines.waive', $f) }}" class="inline" onsubmit="var r=prompt('Reason for waiving?'); if(!r) return false; this.reason.value=r;">
                @csrf
                <input type="hidden" name="reason">
                <button class="text-xs font-semibold text-gold hover:underline">Waive</button>
              </form>
            @else
              <span class="text-xs text-gray-400">{{ $f->waived_reason }}</span>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-12 text-center text-gray-400">No fines</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $fines->links() }}</div>
@endsection
