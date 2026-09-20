@extends('layouts.admin')
@section('title', 'News')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div><h1 class="text-2xl md:text-3xl font-extrabold text-navy">News &amp; Updates</h1><p class="text-sm text-gray-500 mt-1">{{ $news->total() }} posts</p></div>
  <a href="{{ route('principal.news.create') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ New Post</a>
</div>
@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>@endif
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500"><tr class="text-left"><th class="p-3">TITLE</th><th class="p-3">DATE</th><th class="p-3 text-center">STATUS</th><th class="p-3 text-right">ACTION</th></tr></thead>
    <tbody class="divide-y">
      @forelse ($news as $n)
        <tr class="hover:bg-gray-50">
          <td class="p-3"><div class="font-semibold text-navy">{{ $n->title }}</div><div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($n->excerpt, 60) }}</div></td>
          <td class="p-3 text-xs text-gray-500">{{ $n->created_at->format('d M Y') }}</td>
          <td class="p-3 text-center"><span class="px-2 py-1 rounded-full text-xs font-bold {{ $n->published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $n->published ? 'Published' : 'Draft' }}</span></td>
          <td class="p-3 text-right whitespace-nowrap">
            <a href="/news/{{ $n->slug }}" target="_blank" class="text-xs font-semibold text-navy hover:text-gold">View</a>
            <a href="{{ route('principal.news.edit', $n) }}" class="text-xs font-semibold text-navy hover:text-gold ml-2">Edit</a>
            <form method="POST" action="{{ route('principal.news.destroy', $n) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-600 hover:underline ml-2">Delete</button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="p-12 text-center text-gray-400 text-sm">No news yet</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $news->links() }}</div>
@endsection
