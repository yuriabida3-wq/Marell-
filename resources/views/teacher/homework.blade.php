@extends('layouts.teacher')
@section('title', 'Homework')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Homework</h1>
  <p class="text-sm text-gray-500 mt-1">Post assignments — parents get notified via SMS.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  {{-- POST --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">➕ Post Homework</h2>
    <form method="POST" action="{{ route('teacher.homework.store') }}" class="space-y-3">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS *</label>
        <select name="class" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="">-- Select --</option>
          @foreach ($classKeys as $ck)
            <option value="{{ $ck->class }}">{{ $ck->class }} {{ $ck->stream }}</option>
          @endforeach
        </select>
      </div>
      <input type="hidden" name="stream" value="">
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SUBJECT *</label>
        <input name="subject" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">TITLE *</label>
        <input name="title" required maxlength="180" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">DESCRIPTION</label>
        <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:border-gold focus:outline-none"></textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">DUE DATE *</label>
        <input name="due_date" type="date" required min="{{ date('Y-m-d') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">📤 Post + Notify Parents</button>
    </form>
  </div>

  {{-- RECENT --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📋 Recent Posts</h2>
    @if ($homework->count())
      <div class="space-y-3">
        @foreach ($homework as $hw)
          <div class="p-3 rounded-xl bg-gray-50 border-l-4 border-navy">
            <div class="flex justify-between text-xs text-gray-500">
              <span>{{ $hw->class }} {{ $hw->stream }} · {{ $hw->subject }}</span>
              <span>Due: {{ $hw->due_date->format('d M') }}</span>
            </div>
            <div class="font-semibold text-navy text-sm mt-1">{{ $hw->title }}</div>
            @if ($hw->description)
              <div class="text-xs text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($hw->description, 100) }}</div>
            @endif
          </div>
        @endforeach
      </div>
      <div class="mt-4">{{ $homework->links() }}</div>
    @else
      <div class="text-center py-8 text-gray-400 text-sm">
        <div class="text-3xl mb-2">📭</div>
        No homework posted yet.
      </div>
    @endif
  </div>
</div>

@endsection
