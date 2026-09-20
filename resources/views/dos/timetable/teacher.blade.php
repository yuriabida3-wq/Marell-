@extends('layouts.dos')
@section('title', 'Timetable by Teacher')
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.timetable.index') }}" class="text-xs text-gray-500 hover:text-navy">← Timetable</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Timetable by Teacher</h1>
</div>

<div class="bg-white rounded-2xl p-6 shadow mb-4">
  <form method="GET" class="flex flex-col sm:flex-row gap-3">
    <select name="teacher_id" required class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      <option value="">-- Select Teacher --</option>
      @foreach ($teachers as $t)
        <option value="{{ $t->id }}" @selected($teacher && $teacher->id === $t->id)>{{ $t->name }}</option>
      @endforeach
    </select>
    <button class="btn btn-navy">🔍 Show Timetable</button>
  </form>
</div>

@if ($teacher)
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-4 border-b bg-navy text-white">
      <div class="font-extrabold">{{ $teacher->name }}'s Weekly Schedule</div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead class="bg-gray-50">
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
@else
  <div class="text-center py-12 text-gray-400">
    <div class="text-4xl mb-2">👨‍🏫</div>
    Select a teacher to view their weekly schedule.
  </div>
@endif

@endsection
