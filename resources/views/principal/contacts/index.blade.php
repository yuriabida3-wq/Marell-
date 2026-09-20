@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
<div class="mb-6 flex items-center justify-between">
  <div><h1 class="text-2xl md:text-3xl font-extrabold text-navy">Contact Messages</h1><p class="text-sm text-gray-500 mt-1">{{ $unhandled }} unread</p></div>
  <a href="?unhandled=1" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">Show Unread Only</a>
</div>
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500"><tr class="text-left"><th class="p-3">DATE</th><th class="p-3">NAME</th><th class="p-3">SUBJECT</th><th class="p-3">PHONE</th><th class="p-3 text-center">STATUS</th><th class="p-3 text-right">ACTION</th></tr></thead>
    <tbody class="divide-y">
      @forelse ($messages as $m)
        <tr class="hover:bg-gray-50 {{ !$m->handled ? 'bg-blue-50' : '' }}">
          <td class="p-3 text-xs text-gray-500">{{ $m->created_at->format('d M H:i') }}</td>
          <td class="p-3 font-semibold text-navy">{{ $m->name }}</td>
          <td class="p-3 text-gray-600">{{ \Illuminate\Support\Str::limit($m->subject ?: $m->message, 50) }}</td>
          <td class="p-3 text-gray-600 font-mono text-xs">{{ $m->phone ?: '—' }}</td>
          <td class="p-3 text-center">@if($m->handled)<span class="text-xs text-green-700 font-bold">✓ Handled</span>@else<span class="text-xs text-blue-700 font-bold">● New</span>@endif</td>
          <td class="p-3 text-right"><a href="{{ route('principal.contacts.show', $m) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a></td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-12 text-center text-gray-400">No messages</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $messages->links() }}</div>
@endsection
