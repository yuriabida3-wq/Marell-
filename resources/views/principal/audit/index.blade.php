@extends('layouts.admin')
@section('title', 'Audit Log')
@section('content')
<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Audit Log</h1>
  <p class="text-sm text-gray-500 mt-1">Every sensitive action — immutable, timestamped, IP-logged</p>
</div>

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="flex flex-wrap gap-2">
    <select name="action" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm">
      <option value="">All Actions</option>
      @foreach ($actions as $a)
        <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
      @endforeach
    </select>
    <button class="px-4 rounded-xl bg-navy text-white text-sm font-semibold">Filter</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs md:text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">WHEN</th>
          <th class="p-3">WHO</th>
          <th class="p-3">ACTION</th>
          <th class="p-3">TARGET</th>
          <th class="p-3">CHANGES</th>
          <th class="p-3">IP</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($logs as $l)
          <tr class="hover:bg-gray-50">
            <td class="p-3 text-xs text-gray-500">{{ $l->created_at->format('d M H:i:s') }}</td>
            <td class="p-3 font-semibold text-navy">{{ $l->user->name ?? 'System' }}</td>
            <td class="p-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-navy/10 text-navy">{{ $l->action }}</span></td>
            <td class="p-3 text-gray-600 text-xs">
              @if ($l->model_type)
                {{ class_basename($l->model_type) }} #{{ $l->model_id }}
              @else
                —
              @endif
            </td>
            <td class="p-3 text-xs text-gray-600">
              @if ($l->before && $l->after)
                @foreach ($l->after as $k => $v)
                  @if (($l->before[$k] ?? null) !== $v)
                    <div><strong>{{ $k }}:</strong> {{ $l->before[$k] ?? '—' }} → {{ $v }}</div>
                  @endif
                @endforeach
              @elseif ($l->after)
                @foreach ($l->after as $k => $v)
                  <div><strong>{{ $k }}:</strong> {{ $v }}</div>
                @endforeach
              @elseif ($l->reason)
                {{ $l->reason }}
              @else
                —
              @endif
            </td>
            <td class="p-3 font-mono text-xs text-gray-500">{{ $l->ip }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-12 text-center text-gray-400">No activity logged yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
