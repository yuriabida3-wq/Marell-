@extends('layouts.dos')
@section('title', $student->name)
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.students.index') }}" class="text-xs text-gray-500">← All Students</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $student->name }}</h1>
  <p class="text-sm text-gray-500">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</p>
</div>

<div class="flex flex-wrap gap-2 mb-4">
  <a href="{{ route('dos.students.edit', $student) }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">✏️ Edit</a>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">TOTAL FEE</div>
    <div class="text-2xl font-extrabold text-navy mt-1">KES {{ number_format($student->total_fee, 0) }}</div>
  </div>
  <div class="bg-green-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">PAID</div>
    <div class="text-2xl font-extrabold text-green-700 mt-1">KES {{ number_format($student->paid_amount, 0) }}</div>
  </div>
  <div class="bg-red-50 rounded-2xl p-5 shadow">
    <div class="text-xs text-gray-500 tracking-widest">BALANCE</div>
    <div class="text-2xl font-extrabold text-red-600 mt-1">KES {{ number_format($student->balance, 0) }}</div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">👤 Profile</h2>
    <table class="w-full text-sm">
      <tr class="border-b"><td class="py-2 text-gray-500">ADM No</td><td class="py-2 text-right font-mono">{{ $student->adm_no }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Class</td><td class="py-2 text-right">{{ $student->class }} {{ $student->stream }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Parent</td><td class="py-2 text-right">{{ $student->parent_name }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Phone</td><td class="py-2 text-right font-mono text-xs">{{ $student->parent_phone }}</td></tr>
      <tr><td class="py-2 text-gray-500">Status</td><td class="py-2 text-right">{{ ucfirst($student->status) }}</td></tr>
    </table>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📊 Results</h2>
    @if ($results->count())
      <table class="w-full text-sm">
        <thead class="text-xs text-gray-500"><tr><th class="text-left py-1">Subject</th><th class="text-center">Marks</th><th class="text-center">Grade</th></tr></thead>
        <tbody>
          @foreach ($results as $r)
            <tr class="border-t">
              <td class="py-1">{{ $r->subject }}</td>
              <td class="py-1 text-center font-bold">{{ $r->marks }}</td>
              <td class="py-1 text-center font-bold text-navy">{{ $r->grade }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="text-sm text-gray-400 text-center py-6">No results yet</p>
    @endif
  </div>
</div>
@endsection
