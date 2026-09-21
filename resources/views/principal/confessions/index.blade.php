@extends('layouts.admin')
@section('title', 'Anonymous Reports')
@section('content')
<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Anonymous Reports</h1>
  <p class="text-sm text-gray-500 mt-1">{{ $unread }} unread &middot; Visible to Principal only</p>
</div>
@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>@endif
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500">
      <tr class="text-left">
        <th class="p-3">DATE</th>
        <th class="p-3">CATEGORY</th>
        <th class="p-3">MESSAGE</th>
        <th class="p-3 text-center">STATUS</th>
        <th class="p-3 text-right">ACTION</th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @forelse ($items as $c)
        <tr class="hover:bg-gray-50 {{ !$c->read ? 'bg-blue-50 font-semibold' : '' }}">
          <td class="p-3 text-xs text-gray-500">{{ $c->created_at->format('d M H:i') }}</td>
          <td class="p-3"><span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $c->category }}</span></td>
          <td class="p-3 text-gray-700">{{ \Illuminate\Support\Str::limit($c->message, 60) }}</td>
          <td class="p-3 text-center">
            @if(!$c->read)<span class="text-xs text-blue-700 font-bold">NEW</span>@else<span class="text-xs text-gray-400">Read</span>@endif
          </td>
          <td class="p-3 text-right"><a href="{{ route('principal.confessions.show', $c) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a></td>
        </tr>
      @empty
        <tr><td colspan="5" class="p-12 text-center text-gray-400">No reports</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
