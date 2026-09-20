@extends('layouts.public')
@section('title', 'Timetable')
@section('content')
<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">TIMETABLE</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Class Timetables</h1>
  </div>
</section>
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="card p-4 mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
      <select name="class_id" required class="flex-1 min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        <option value="">-- Select Class --</option>
        @foreach ($classes as $c)
          <option value="{{ $c->id }}" @selected($class && $class->id === $c->id)>{{ $c->label() }}</option>
        @endforeach
      </select>
      <button class="btn btn-gold">View Timetable</button>
    </form>
  </div>

  @if ($class && count($grid))
    <div class="card overflow-hidden">
      <div class="p-4 bg-navy text-white font-bold">{{ $class->label() }}</div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead class="bg-gray-50"><tr><th class="p-2 text-left">PERIOD</th>@foreach(['Mon','Tue','Wed','Thu','Fri'] as $d)<th class="p-2">{{ $d }}</th>@endforeach</tr></thead>
          <tbody class="divide-y">
            @for ($p = 1; $p <= 8; $p++)
              <tr><td class="p-2 font-bold text-navy">P{{ $p }}</td>
                @foreach(['Mon','Tue','Wed','Thu','Fri'] as $d)
                  @php $s = $grid[$d][$p] ?? null; @endphp
                  <td class="p-1.5 text-center">@if($s)<div class="rounded bg-gold/10 border border-gold/40 p-1.5"><div class="font-bold text-navy text-[11px]">{{ $s->subject }}</div><div class="text-[9px] text-gray-600">{{ $s->teacher->name ?? '—' }}</div></div>@else<span class="text-gray-300">—</span>@endif</td>
                @endforeach
              </tr>
            @endfor
          </tbody>
        </table>
      </div>
    </div>
  @endif
</section>
@endsection
