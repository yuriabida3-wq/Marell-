@extends('layouts.dos')
@section('title', 'Marks Portal Controls')
@section('content')

<div class="mb-6">
  <a href="{{ route('dos.exams.show', $exam) }}" class="text-xs text-gray-500">← Exam</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $exam->name }}</h1>
  <p class="text-sm text-gray-500">{{ $exam->term }} · {{ $exam->year }} · Status: <span class="font-semibold">{{ strtoupper($exam->status) }}</span></p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="flex flex-wrap gap-2 mb-4">
  <form method="POST" action="{{ route('dos.exams.controls.open-all', $exam) }}" onsubmit="return confirm('Open marks entry for ALL classes?')">
    @csrf
    <button class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold text-sm hover:scale-105 transition">🔓 Open All Classes</button>
  </form>
  <form method="POST" action="{{ route('dos.exams.controls.close-all', $exam) }}" onsubmit="return confirm('Close marks entry for ALL classes?')">
    @csrf
    <button class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold text-sm hover:scale-105 transition">🔒 Close All Classes</button>
  </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- Per-class toggles --}}
  <div class="lg:col-span-2 bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b">
      <div class="font-extrabold text-navy text-lg">Per-Class Marks Portal</div>
      <div class="text-xs text-gray-500">Open a class → teachers can enter marks. Close → they can't.</div>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">CLASS</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($classes as $c)
          @php $row = $latest[$c->id] ?? null; $isOpen = $row && $row->action === 'opened'; @endphp
          <tr>
            <td class="p-3 font-semibold text-navy">{{ $c->label() }}</td>
            <td class="p-3 text-center">
              @if ($isOpen)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">🔓 OPEN</span>
              @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">🔒 CLOSED</span>
              @endif
            </td>
            <td class="p-3 text-right">
              @if ($isOpen)
                <form method="POST" action="{{ route('dos.exams.controls.close', $exam) }}" class="inline">
                  @csrf
                  <input type="hidden" name="class_id" value="{{ $c->id }}">
                  <button class="text-xs font-semibold text-red-600 hover:underline">Close</button>
                </form>
              @else
                <form method="POST" action="{{ route('dos.exams.controls.open', $exam) }}" class="inline">
                  @csrf
                  <input type="hidden" name="class_id" value="{{ $c->id }}">
                  <button class="text-xs font-semibold text-green-600 hover:underline">Open</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Audit trail --}}
  <div class="lg:col-span-1 bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b">
      <div class="font-extrabold text-navy text-lg">📋 Audit Trail</div>
      <div class="text-xs text-gray-500">Last 20 actions</div>
    </div>
    <div class="p-4 space-y-3 max-h-[600px] overflow-y-auto">
      @forelse ($audit as $a)
        <div class="text-xs p-3 rounded-lg bg-gray-50 border-l-4 {{ $a->action === 'opened' ? 'border-green-500' : 'border-red-500' }}">
          <div class="flex justify-between">
            <span class="font-bold {{ $a->action === 'opened' ? 'text-green-700' : 'text-red-700' }}">{{ strtoupper($a->action) }}</span>
            <span class="text-gray-400">{{ $a->created_at->format('d M H:i') }}</span>
          </div>
          <div class="text-gray-700 mt-1">{{ $a->class }}{{ $a->stream ? ' '.$a->stream : '' }}</div>
          <div class="text-gray-500">by {{ $a->actor->name ?? 'System' }} · {{ $a->actor_ip ?? '—' }}</div>
        </div>
      @empty
        <div class="text-center text-gray-400 py-8 text-xs">No actions yet</div>
      @endforelse
    </div>
  </div>
</div>

@endsection
