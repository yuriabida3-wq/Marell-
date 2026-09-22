@extends('layouts.admin')
@section('title', 'Fee Autopilot')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">🤖 Fee Autopilot</h1>
    <p class="text-sm text-gray-500 mt-1">Auto-escalating reminders for unpaid fees. Runs daily at 8am.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('principal.fee-autopilot.settings') }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">⚙️ Settings</a>
    <form method="POST" action="{{ route('principal.fee-autopilot.run') }}" class="inline">
      @csrf
      <button class="px-4 py-2 rounded-xl bg-gold text-navy text-sm font-bold">⚡ Run Now</button>
    </form>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
  <div class="bg-gradient-to-br from-navy to-[#082f6f] rounded-2xl p-5 text-white shadow">
    <div class="text-[10px] tracking-widest text-gold font-bold">TODAY</div>
    <div class="text-2xl font-extrabold mt-1">{{ $stats['today'] }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest text-gray-500 font-bold">THIS WEEK</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $stats['week'] }}</div></div>
  <div class="bg-white rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest text-gray-500 font-bold">THIS MONTH</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $stats['month'] }}</div></div>
  <div class="bg-green-50 rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest text-green-700 font-bold">SENT</div><div class="text-2xl font-extrabold text-green-700 mt-1">{{ $stats['sent'] }}</div></div>
  <div class="bg-red-50 rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest text-red-700 font-bold">FAILED</div><div class="text-2xl font-extrabold text-red-600 mt-1">{{ $stats['failed'] }}</div></div>
  <div class="bg-yellow-50 rounded-2xl p-5 shadow"><div class="text-[10px] tracking-widest text-yellow-700 font-bold">PENDING</div><div class="text-2xl font-extrabold text-yellow-600 mt-1">{{ $stats['pending'] }}</div></div>
</div>

{{-- STAGE BREAKDOWN --}}
<div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
  <div class="p-5 border-b">
    <div class="font-extrabold text-navy">📊 Escalation Stages</div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">STAGE</th>
          <th class="p-3">NAME</th>
          <th class="p-3">CHANNEL</th>
          <th class="p-3 text-center">DAY OFFSET</th>
          <th class="p-3 text-center">TRIGGERED</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($stages as $s)
          @php $count = $byStage->firstWhere('stage', $s->stage)->total ?? 0; @endphp
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-bold text-navy">Stage {{ $s->stage }}</td>
            <td class="p-3 text-gray-700">{{ $s->name }}</td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-navy/10 text-navy">{{ strtoupper($s->channel) }}</span>
            </td>
            <td class="p-3 text-center text-xs">
              @if ($s->days_offset < 0) {{ abs($s->days_offset) }} days before
              @elseif ($s->days_offset === 0) Due day
              @else {{ $s->days_offset }} days after
              @endif
            </td>
            <td class="p-3 text-center font-bold">{{ $count }}</td>
            <td class="p-3 text-right">
              <a href="{{ route('principal.fee-autopilot.index', ['stage' => $s->stage]) }}" class="text-xs font-semibold text-navy hover:text-gold">Filter</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- RECENT ESCALATIONS --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="p-5 border-b flex items-center justify-between">
    <div class="font-extrabold text-navy">📋 Recent Escalations</div>
    @if ($stage)
      <a href="{{ route('principal.fee-autopilot.index') }}" class="text-xs text-gray-500">Clear filter</a>
    @endif
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">WHEN</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3 text-center">STAGE</th>
          <th class="p-3">CHANNEL</th>
          <th class="p-3">MESSAGE</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($escalations as $e)
          <tr class="hover:bg-gray-50">
            <td class="p-3 text-xs text-gray-500">{{ $e->created_at->format('d M H:i') }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy text-sm">{{ $e->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500 font-mono">{{ $e->student->adm_no ?? '' }}</div>
            </td>
            <td class="p-3 text-center">
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-navy text-white">S{{ $e->stage }}</span>
            </td>
            <td class="p-3 text-xs">{{ strtoupper($e->channel) }}</td>
            <td class="p-3 text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($e->message, 50) }}</td>
            <td class="p-3 text-center">
              <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $e->statusColor() }}">{{ strtoupper($e->status) }}</span>
            </td>
            <td class="p-3 text-right">
              <a href="{{ route('principal.fee-autopilot.student', $e->student_id) }}" class="text-xs font-semibold text-navy hover:text-gold">Timeline</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="p-12 text-center text-gray-400">No escalations yet. Click "Run Now" to trigger.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-4">{{ $escalations->links() }}</div>
</div>
@endsection
