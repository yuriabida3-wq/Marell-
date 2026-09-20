@extends('layouts.teacher')
@section('title', 'My Classes')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">My Classes</h1>
  <p class="text-sm text-gray-500 mt-1">Classes you teach, with student lists.</p>
</div>

@forelse ($classes as $c)
  <div class="bg-white rounded-2xl p-5 shadow mb-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
      <div>
        <h2 class="font-extrabold text-navy text-lg">{{ $c['class'] }} {{ $c['stream'] }}</h2>
        <div class="text-xs text-gray-500">{{ $c['students']->count() }} students · Subjects: {{ $c['subjects']->implode(', ') }}</div>
      </div>
      <a href="{{ route('teacher.classListPdf') }}?class={{ urlencode($c['class']) }}&stream={{ urlencode($c['stream']) }}"
         class="px-4 py-2 rounded-xl bg-green-600 text-white text-xs font-semibold hover:scale-105 transition">📄 Class List PDF</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-xs md:text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-2">#</th>
            <th class="p-2">ADM</th>
            <th class="p-2">NAME</th>
            <th class="p-2">PARENT</th>
            <th class="p-2 text-right">BALANCE</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach ($c['students'] as $i => $s)
            <tr>
              <td class="p-2 text-gray-500">{{ $i+1 }}</td>
              <td class="p-2 font-mono text-navy">{{ $s->adm_no }}</td>
              <td class="p-2 font-semibold text-navy">{{ $s->name }}</td>
              <td class="p-2 text-gray-600">{{ $s->parent_name }}</td>
              <td class="p-2 text-right {{ $s->balance > 0 ? 'text-red-600' : 'text-green-600' }} font-bold">KES {{ number_format($s->balance, 0) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@empty
  <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow">
    <div class="text-4xl mb-2">🎓</div>
    No classes assigned. Wait for DOS to assign you timetable slots.
  </div>
@endforelse

@endsection
