@extends(role_layout())
@section('title', 'Loans')
@section('content')

<div class="mb-6">
  <a href="{{ route('library.index') }}" class="text-xs text-gray-500">← Library</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">📋 Book Loans</h1>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif

<div class="flex flex-wrap gap-2 mb-4">
  @foreach (['active' => '📖 Active', 'overdue' => '⚠️ Overdue', 'returned' => '✓ Returned'] as $key => $label)
    <a href="{{ route('library.loans', ['filter' => $key]) }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ $filter === $key ? 'bg-navy text-white' : 'bg-gray-100 text-navy' }}">{{ $label }}</a>
  @endforeach
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">STUDENT</th>
        <th class="p-3">BOOK</th>
        <th class="p-3">ISSUED</th>
        <th class="p-3">DUE</th>
        <th class="p-3 text-center">STATUS</th>
        <th class="p-3 text-right">FINE</th>
        <th class="p-3 text-right">ACTION</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($loans as $l)
        <tr class="hover:bg-gray-50">
          <td class="p-3">
            <div class="font-semibold text-navy text-sm">{{ $l->student->name ?? '—' }}</div>
            <div class="text-xs text-gray-500 font-mono">{{ $l->student->adm_no ?? '' }}</div>
          </td>
          <td class="p-3 text-gray-700">{{ $l->book->title ?? '—' }}</td>
          <td class="p-3 text-xs text-gray-500">{{ $l->issued_at->format('d M Y') }}</td>
          <td class="p-3 text-xs">{{ $l->due_at->format('d M Y') }}</td>
          <td class="p-3 text-center">
            @if ($l->returned_at)
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Returned</span>
            @elseif ($l->due_at->isPast())
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Overdue {{ $l->daysOverdue() }}d</span>
            @else
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Out</span>
            @endif
          </td>
          <td class="p-3 text-right">
            @if ($l->fine > 0)
              <span class="{{ $l->fine_paid ? 'text-gray-400 line-through' : 'text-red-600 font-bold' }}">KES {{ number_format($l->fine, 0) }}</span>
            @else
              —
            @endif
          </td>
          <td class="p-3 text-right whitespace-nowrap">
            @if (!$l->returned_at)
              <form method="POST" action="{{ route('library.loans.return', $l) }}" class="inline">
                @csrf
                <button class="text-xs font-semibold text-green-600 hover:underline">Return</button>
              </form>
            @elseif ($l->fine > 0 && !$l->fine_paid)
              <form method="POST" action="{{ route('library.loans.fine-paid', $l) }}" class="inline">
                @csrf
                <button class="text-xs font-semibold text-navy hover:underline">Mark Fine Paid</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="p-12 text-center text-gray-400">No loans</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $loans->links() }}</div>
@endsection
