@extends('layouts.admin')
@section('title', 'Chronic Absentees')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.attendance.index') }}" class="text-xs text-gray-500">← Attendance</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">⚠️ Chronic Absentees</h1>
  <p class="text-sm text-gray-500">Students with 3+ absences in last {{ $days }} days (since {{ \Carbon\Carbon::parse($from)->format('d M Y') }})</p>
</div>

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="flex gap-2">
    <select name="days" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      @foreach ([7 => 'Last 7 days', 14 => 'Last 14 days', 30 => 'Last 30 days', 60 => 'Last 60 days', 90 => 'Last 90 days'] as $k => $v)
        <option value="{{ $k }}" @selected($days === $k)>{{ $v }}</option>
      @endforeach
    </select>
    <button class="px-6 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Apply</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-red-50 text-xs text-red-700">
      <tr class="text-left">
        <th class="p-3">STUDENT</th>
        <th class="p-3">CLASS</th>
        <th class="p-3">PARENT</th>
        <th class="p-3">PHONE</th>
        <th class="p-3 text-center">ABSENCES</th>
        <th class="p-3 text-right">ACTION</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($absentees as $a)
        <tr class="hover:bg-gray-50">
          <td class="p-3">
            <div class="font-semibold text-navy">{{ $a->student->name ?? '—' }}</div>
            <div class="text-xs text-gray-500 font-mono">{{ $a->student->adm_no ?? '' }}</div>
          </td>
          <td class="p-3 text-gray-600">{{ $a->student->class ?? '' }}</td>
          <td class="p-3 text-gray-600">{{ $a->student->parent_name ?? '' }}</td>
          <td class="p-3 font-mono text-xs">{{ $a->student->parent_phone ?? '' }}</td>
          <td class="p-3 text-center">
            <span class="px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700">{{ $a->absent_days }}</span>
          </td>
          <td class="p-3 text-right">
            <a href="tel:{{ $a->student->parent_phone }}" class="text-xs font-semibold text-navy hover:text-gold">📞 Call Parent</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-12 text-center text-gray-400">🎉 No chronic absentees — great attendance!</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $absentees->links() }}</div>
@endsection
