@extends('layouts.teacher')
@section('title', 'Lesson Plans')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">📚 Lesson Plans</h1>
  <p class="text-sm text-gray-500 mt-1">Submit weekly plans. DOS approves them.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1">
    <div class="bg-white rounded-2xl p-6 shadow sticky top-20">
      <h2 class="font-extrabold text-navy mb-4">➕ New Lesson Plan</h2>
      <form method="POST" action="{{ route('teacher.lesson-plans.store') }}" class="space-y-3">
        @csrf
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS *</label>
          <select name="class" required class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
            <option value="">-- Select --</option>
            @foreach ($classKeys as $ck)
              <option value="{{ $ck->class }}">{{ $ck->class }} {{ $ck->stream }}</option>
            @endforeach
          </select>
        </div>
        <input type="hidden" name="stream" value="">
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SUBJECT *</label>
          <input name="subject" required class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">WEEK STARTING *</label>
          <input name="week_starting" type="date" value="{{ now()->startOfWeek()->toDateString() }}" required class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TOPIC *</label>
          <input name="topic" required maxlength="200" class="w-full min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
        </div>
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">OBJECTIVES</label>
          <textarea name="objectives" rows="3" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm"></textarea>
        </div>
        <div>
          <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ACTIVITIES</label>
          <textarea name="activities" rows="3" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm"></textarea>
        </div>
        <div class="flex gap-2">
          <button name="submit" value="0" class="flex-1 min-h-[42px] rounded-xl bg-gray-200 text-navy font-semibold text-sm">Save Draft</button>
          <button name="submit" value="1" class="flex-1 min-h-[42px] rounded-xl bg-gold text-navy font-bold text-sm">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <div class="lg:col-span-2">
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-3">WEEK</th>
            <th class="p-3">CLASS</th>
            <th class="p-3">SUBJECT</th>
            <th class="p-3">TOPIC</th>
            <th class="p-3 text-center">STATUS</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($plans as $p)
            <tr class="hover:bg-gray-50">
              <td class="p-3 text-xs">{{ $p->week_starting->format('d M') }}</td>
              <td class="p-3 text-gray-600">{{ $p->class }}</td>
              <td class="p-3 text-gray-600">{{ $p->subject }}</td>
              <td class="p-3 font-semibold text-navy">{{ $p->topic }}</td>
              <td class="p-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $p->statusColor() }}">{{ strtoupper($p->status) }}</span>
                @if ($p->review_notes)
                  <div class="text-xs text-gray-500 mt-1">{{ $p->review_notes }}</div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-12 text-center text-gray-400">No lesson plans yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $plans->links() }}</div>
  </div>
</div>
@endsection
