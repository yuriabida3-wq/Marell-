@extends('layouts.dos')
@section('title', 'Teacher Subjects')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Teacher Subject Assignments</h1>
  <p class="text-sm text-gray-500 mt-1">Define what each teacher teaches. Timetable generator uses this.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- LEFT: Bulk assign --}}
  <div class="lg:col-span-1">
    <div class="bg-white rounded-2xl p-6 shadow sticky top-20">
      <h2 class="font-extrabold text-navy mb-4">➕ Assign Subjects</h2>

      <form method="POST" action="{{ route('dos.teacher-subjects.bulk') }}" class="space-y-4">
        @csrf

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TEACHER *</label>
          <select name="teacher_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
            <option value="">-- Select --</option>
            @foreach ($teachers as $t)
              <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SUBJECTS * (multi-select)</label>
          <div class="max-h-56 overflow-y-auto border-2 border-gray-200 rounded-xl p-3 space-y-1">
            @foreach ($subjects as $s)
              <label class="flex items-center gap-2 text-sm py-1">
                <input type="checkbox" name="subjects[]" value="{{ $s }}" class="w-4 h-4">
                {{ $s }}
              </label>
            @endforeach
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASSES * (multi-select)</label>
          <div class="max-h-56 overflow-y-auto border-2 border-gray-200 rounded-xl p-3 space-y-1">
            @forelse ($classes as $c)
              <label class="flex items-center gap-2 text-sm py-1">
                <input type="checkbox" name="classes[]" value="{{ $c->name }}|{{ $c->stream }}" class="w-4 h-4">
                {{ $c->label() }}
              </label>
            @empty
              <div class="text-xs text-gray-400">No classes yet</div>
            @endforelse
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PERIODS / WEEK *</label>
          <input type="number" name="periods_per_week" value="4" min="1" max="12" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        </div>

        <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">Assign</button>
      </form>
    </div>
  </div>

  {{-- RIGHT: Existing assignments --}}
  <div class="lg:col-span-2 space-y-4">
    @forelse ($teachers as $t)
      @php $list = $assignments[$t->id] ?? collect(); @endphp
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center font-bold">
              {{ strtoupper(substr($t->name, 0, 1)) }}
            </div>
            <div>
              <div class="font-bold text-navy">{{ $t->name }}</div>
              <div class="text-xs text-gray-500">{{ $list->count() }} assignments</div>
            </div>
          </div>
          @if ($list->count())
            <form method="POST" action="{{ route('dos.teacher-subjects.clear', $t) }}" onsubmit="return confirm('Clear all subjects for {{ $t->name }}?')">
              @csrf @method('DELETE')
              <button class="text-xs text-red-600 hover:underline">Clear All</button>
            </form>
          @endif
        </div>

        @if ($list->isEmpty())
          <div class="p-6 text-center text-sm text-gray-400">No subjects assigned yet</div>
        @else
          <table class="w-full text-sm">
            <thead class="text-xs text-gray-500">
              <tr class="text-left">
                <th class="p-2">SUBJECT</th>
                <th class="p-2">CLASS</th>
                <th class="p-2 text-center">PERIODS/WK</th>
                <th class="p-2 text-right">ACTION</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach ($list as $a)
                <tr>
                  <td class="p-2 font-semibold text-navy">{{ $a->subject }}</td>
                  <td class="p-2 text-gray-600">{{ $a->class }}{{ $a->stream ? ' '.$a->stream : '' }}</td>
                  <td class="p-2 text-center">{{ $a->periods_per_week }}</td>
                  <td class="p-2 text-right">
                    <form method="POST" action="{{ route('dos.teacher-subjects.destroy', $a) }}" class="inline">
                      @csrf @method('DELETE')
                      <button class="text-xs text-red-600 hover:underline">Remove</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    @empty
      <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow">No teachers registered yet</div>
    @endforelse
  </div>
</div>

@endsection
