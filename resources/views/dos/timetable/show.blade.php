@extends('layouts.dos')
@section('title', 'Timetable — ' . $class->label())
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <a href="{{ route('dos.timetable.index') }}" class="text-xs text-gray-500 hover:text-navy">← Timetable</a>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $class->label() }} Timetable</h1>
    <p class="text-sm text-gray-500">{{ $class->studentCount() }} students · {{ $class->level ?: 'No level' }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('dos.timetable.pdf', $class) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm hover:scale-105 transition">📄 Download PDF</a>
    <a href="https://wa.me/254700000000?text={{ urlencode('Check our class timetable: ' . url()->current()) }}"
       target="_blank" class="px-4 py-2 rounded-xl bg-green-500 text-white font-semibold text-sm hover:scale-105 transition">💬 WhatsApp</a>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif

{{-- TIMETABLE GRID --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs md:text-sm">
      <thead class="bg-navy text-white">
        <tr>
          <th class="p-2 text-left w-16">PERIOD</th>
          @foreach (['Mon','Tue','Wed','Thu','Fri'] as $d)
            <th class="p-2 text-center">{{ $d }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody class="divide-y">
        @for ($p = 1; $p <= 8; $p++)
          @if ($p === 4)
            <tr class="bg-yellow-50">
              <td class="p-2 font-bold text-yellow-700 text-[10px]">10:00</td>
              <td colspan="5" class="p-2 text-center text-yellow-700 font-semibold text-[10px]">☕ SHORT BREAK (10:00 – 10:20)</td>
            </tr>
          @endif
          @if ($p === 7)
            <tr class="bg-blue-50">
              <td class="p-2 font-bold text-blue-700 text-[10px]">12:20</td>
              <td colspan="5" class="p-2 text-center text-blue-700 font-semibold text-[10px]">🍽️ LUNCH (12:20 – 14:00)</td>
            </tr>
          @endif
          <tr class="{{ $p % 2 ? 'bg-white' : 'bg-gray-50' }}">
            <td class="p-2 font-bold text-navy text-[10px]">P{{ $p }}</td>
            @foreach (['Mon','Tue','Wed','Thu','Fri'] as $d)
              @php $slot = $grid[$d][$p] ?? null; @endphp
              <td class="p-1.5 text-center align-top">
                @if ($slot)
                  <div class="rounded-lg bg-gold/10 border border-gold/40 p-1.5">
                    <div class="font-bold text-navy text-[11px] leading-tight">{{ $slot->subject }}</div>
                    <div class="text-[9px] text-gray-600 leading-tight mt-0.5">{{ $slot->teacher->name ?? '—' }}</div>
                  </div>
                @else
                  <span class="text-gray-300 text-xs">—</span>
                @endif
              </td>
            @endforeach
          </tr>
        @endfor
      </tbody>
    </table>
  </div>
</div>

{{-- TEACHERS LEGEND --}}
@php
  $teacherNames = collect($grid)->flatMap(fn ($r) => $r)->map(fn ($s) => $s->teacher->name ?? null)->filter()->unique()->values();
@endphp
@if ($teacherNames->count())
  <div class="mt-4 bg-white rounded-2xl p-4 shadow">
    <div class="text-xs font-semibold text-gray-500 mb-2 tracking-widest">TEACHERS</div>
    <div class="flex flex-wrap gap-2">
      @foreach ($teacherNames as $t)
        <span class="px-3 py-1 rounded-full bg-navy/10 text-navy text-xs font-semibold">{{ $t }}</span>
      @endforeach
    </div>
  </div>
@endif

@endsection
