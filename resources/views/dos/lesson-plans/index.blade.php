@extends('layouts.dos')
@section('title', 'Lesson Plan Reviews')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">📚 Lesson Plan Reviews</h1>
  <p class="text-sm text-gray-500 mt-1">Approve or reject teacher submissions</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="grid grid-cols-4 gap-3 mb-4">
  <a href="?status=draft" class="bg-white rounded-2xl p-4 shadow {{ $status === 'draft' ? 'ring-2 ring-gold' : '' }}"><div class="text-[10px] text-gray-500 tracking-widest font-bold">DRAFT</div><div class="text-2xl font-extrabold text-gray-600 mt-1">{{ $counts['draft'] }}</div></a>
  <a href="?status=submitted" class="bg-white rounded-2xl p-4 shadow {{ $status === 'submitted' ? 'ring-2 ring-gold' : '' }}"><div class="text-[10px] text-blue-700 tracking-widest font-bold">SUBMITTED</div><div class="text-2xl font-extrabold text-blue-700 mt-1">{{ $counts['submitted'] }}</div></a>
  <a href="?status=approved" class="bg-white rounded-2xl p-4 shadow {{ $status === 'approved' ? 'ring-2 ring-gold' : '' }}"><div class="text-[10px] text-green-700 tracking-widest font-bold">APPROVED</div><div class="text-2xl font-extrabold text-green-700 mt-1">{{ $counts['approved'] }}</div></a>
  <a href="?status=rejected" class="bg-white rounded-2xl p-4 shadow {{ $status === 'rejected' ? 'ring-2 ring-gold' : '' }}"><div class="text-[10px] text-red-700 tracking-widest font-bold">REJECTED</div><div class="text-2xl font-extrabold text-red-600 mt-1">{{ $counts['rejected'] }}</div></div></a>
</div>

<div class="space-y-3">
  @forelse ($plans as $p)
    <div class="bg-white rounded-2xl p-5 shadow">
      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-3">
        <div class="flex-1">
          <div class="font-bold text-navy">{{ $p->teacher->name ?? '—' }} — {{ $p->subject }}</div>
          <div class="text-xs text-gray-500">{{ $p->class }} · Week of {{ $p->week_starting->format('d M Y') }}</div>
          <div class="font-semibold text-navy mt-2">Topic: {{ $p->topic }}</div>
          @if ($p->objectives)
            <div class="text-xs text-gray-600 mt-2"><strong>Objectives:</strong> {{ $p->objectives }}</div>
          @endif
          @if ($p->activities)
            <div class="text-xs text-gray-600 mt-1"><strong>Activities:</strong> {{ $p->activities }}</div>
          @endif
        </div>
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $p->statusColor() }}">{{ strtoupper($p->status) }}</span>
      </div>

      @if ($p->status === 'submitted')
        <form method="POST" action="{{ route('dos.lesson-plans.review', $p) }}" class="flex flex-wrap gap-2 items-center">
          @csrf
          <input name="notes" placeholder="Feedback (optional)" class="flex-1 min-h-[38px] rounded-lg border-2 border-gray-200 px-3 text-xs">
          <button name="action" value="approve" class="px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold">✅ Approve</button>
          <button name="action" value="reject" class="px-4 py-2 rounded-lg bg-red-600 text-white text-xs font-bold">❌ Reject</button>
        </form>
      @elseif ($p->review_notes)
        <div class="text-xs text-gray-500 italic">Reviewed: {{ $p->review_notes }}</div>
      @endif
    </div>
  @empty
    <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow">
      <div class="text-4xl mb-2">📭</div>
      No lesson plans in this status
    </div>
  @endforelse
</div>
<div class="mt-4">{{ $plans->links() }}</div>
@endsection
