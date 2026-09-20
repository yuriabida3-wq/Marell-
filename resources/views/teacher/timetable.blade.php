@extends('layouts.teacher')
@section('title', 'My Timetable')
@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">My Timetable</h1>
    <p class="text-sm text-gray-500 mt-1">Your weekly schedule</p>
  </div>
  <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm">📄 Print</button>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs">
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
          <tr class="{{ $p % 2 ? 'bg-white' : 'bg-gray-50' }}">
            <td class="p-2 font-bold text-navy">P{{ $p }}</td>
            @foreach (['Mon','Tue','Wed','Thu','Fri'] as $d)
              @php $slot = $grid[$d][$p] ?? null; @endphp
              <td class="p-1.5 text-center align-top">
                @if ($slot)
                  <div class="rounded-lg bg-gold/10 border border-gold/40 p-1.5">
                    <div class="font-bold text-navy text-[11px]">{{ $slot->subject }}</div>
                    <div class="text-[9px] text-gray-600">{{ $slot->class }} {{ $slot->stream }}</div>
                  </div>
                @else
                  <span class="text-gray-300">—</span>
                @endif
              </td>
            @endforeach
          </tr>
        @endfor
      </tbody>
    </table>
  </div>
</div>

@endsection
