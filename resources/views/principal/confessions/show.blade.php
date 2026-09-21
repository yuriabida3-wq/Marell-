@extends('layouts.admin')
@section('title', 'Report')
@section('content')
<div class="mb-6">
  <a href="{{ route('principal.confessions.index') }}" class="text-xs text-gray-500">Back to reports</a>
  <h1 class="text-2xl font-extrabold text-navy mt-1">Anonymous Report</h1>
</div>
@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>@endif
<div class="bg-white rounded-2xl p-6 shadow max-w-3xl">
  <div class="flex justify-between text-sm text-gray-500 mb-4">
    <span>Category: <strong class="text-navy">{{ $confession->category }}</strong></span>
    <span>{{ $confession->created_at->format('d M Y H:i') }}</span>
  </div>
  <div class="bg-gray-50 rounded-xl p-5 text-sm whitespace-pre-wrap">{{ $confession->message }}</div>
  @if ($confession->contact)
    <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-500 rounded text-sm"><strong>Reporter contact:</strong> {{ $confession->contact }}</div>
  @else
    <div class="mt-4 text-xs text-gray-500 italic">Reporter chose to stay anonymous</div>
  @endif
  <div class="mt-6 flex flex-wrap gap-2">
    <form method="POST" action="{{ route('principal.confessions.flag', $confession) }}" class="contents">
      @csrf
      <button class="px-4 py-2 rounded-xl {{ $confession->flagged ? 'bg-red-600 text-white' : 'bg-gray-200 text-navy' }} text-sm font-semibold">{{ $confession->flagged ? 'Flagged' : 'Flag for follow-up' }}</button>
    </form>
    <form method="POST" action="{{ route('principal.confessions.destroy', $confession) }}" class="contents" onsubmit="return confirm('Delete permanently?')">
      @csrf @method('DELETE')
      <button class="px-4 py-2 rounded-xl bg-red-50 text-red-600 text-sm font-semibold">Delete</button>
    </form>
  </div>
</div>
@endsection
