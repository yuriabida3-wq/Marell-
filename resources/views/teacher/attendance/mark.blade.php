@extends('layouts.teacher')
@section('title', 'Mark Attendance')
@section('content')

<div class="mb-4">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">✅ Mark Attendance</h1>
  <p class="text-sm text-gray-500 mt-1">Tap each student's status. Save once. Parents get SMS instantly.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <select name="class" required class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">-- Class --</option>
      @foreach ($classKeys as $ck)
        <option value="{{ $ck->class }}" @selected($selectedClass === $ck->class)>{{ $ck->class }} {{ $ck->stream }}</option>
      @endforeach
    </select>
    <input type="hidden" name="stream" value="{{ $selectedStream }}">
    <input type="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm">
    <button class="md:col-span-2 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm">Load Students</button>
  </form>
</div>

@if ($selectedClass && $students->count())
  <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-4">
    <div class="bg-green-50 rounded-xl p-3 text-center"><div class="text-xs text-gray-500">Present</div><div class="font-bold text-green-700 text-lg">{{ $summary['present'] }}</div></div>
    <div class="bg-red-50 rounded-xl p-3 text-center"><div class="text-xs text-gray-500">Absent</div><div class="font-bold text-red-700 text-lg">{{ $summary['absent'] }}</div></div>
    <div class="bg-yellow-50 rounded-xl p-3 text-center"><div class="text-xs text-gray-500">Late</div><div class="font-bold text-yellow-700 text-lg">{{ $summary['late'] }}</div></div>
    <div class="bg-blue-50 rounded-xl p-3 text-center"><div class="text-xs text-gray-500">Excused</div><div class="font-bold text-blue-700 text-lg">{{ $summary['excused'] }}</div></div>
    <div class="bg-purple-50 rounded-xl p-3 text-center"><div class="text-xs text-gray-500">Sick</div><div class="font-bold text-purple-700 text-lg">{{ $summary['sick'] }}</div></div>
    <div class="bg-gray-50 rounded-xl p-3 text-center"><div class="text-xs text-gray-500">Unmarked</div><div class="font-bold text-gray-700 text-lg">{{ $summary['unmarked'] }}</div></div>
  </div>

  <div class="flex gap-2 mb-3">
    <button onclick="markAll('present')" class="px-3 py-1.5 rounded-lg bg-green-100 text-green-700 text-xs font-bold">✅ All Present</button>
    <button onclick="markAll('absent')" class="px-3 py-1.5 rounded-lg bg-red-100 text-red-700 text-xs font-bold">❌ All Absent</button>
  </div>

  <form method="POST" action="{{ route('teacher.attendance.store') }}">
    @csrf
    <input type="hidden" name="class" value="{{ $selectedClass }}">
    <input type="hidden" name="stream" value="{{ $selectedStream }}">
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-3">STUDENT</th>
            <th class="p-3 text-center">STATUS</th>
            <th class="p-3">REASON (if absent/late)</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach ($students as $s)
            @php $existing = $existing[$s->id] ?? null; @endphp
            <tr class="attendance-row" data-student="{{ $s->id }}">
              <td class="p-3">
                <div class="font-semibold text-navy text-sm">{{ $s->name }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ $s->adm_no }}</div>
              </td>
              <td class="p-3">
                <div class="flex flex-wrap gap-1 justify-center">
                  @foreach (['present' => '✅', 'absent' => '❌', 'late' => '⏰', 'excused' => '📝', 'sick' => '🤒'] as $st => $icon)
                    <label class="cursor-pointer">
                      <input type="radio" name="status[{{ $s->id }}]" value="{{ $st }}" class="peer sr-only status-radio"
                             data-student="{{ $s->id }}"
                             {{ ($existing->status ?? 'present') === $st ? 'checked' : '' }}>
                      <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg border-2 border-gray-200 text-sm peer-checked:bg-gold peer-checked:border-gold peer-checked:text-navy peer-checked:font-bold">
                        {{ $icon }}
                      </span>
                    </label>
                  @endforeach
                </div>
              </td>
              <td class="p-3">
                <input name="reason[{{ $s->id }}]" value="{{ $existing->reason ?? '' }}" placeholder="e.g. Sick, family emergency"
                       class="w-full min-h-[36px] rounded-lg border-2 border-gray-200 px-2 text-xs">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <button class="mt-4 w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold shadow-lg sticky bottom-4">💾 Save Attendance</button>
  </form>
@elseif ($selectedClass)
  <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded text-sm">
    No active students in this class.
  </div>
@endif

@push('scripts')
<script>
function markAll(status) {
  document.querySelectorAll(`input.status-radio[value="${status}"]`).forEach(r => r.checked = true);
}
</script>
@endpush
@endsection
