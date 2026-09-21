@extends(role_layout())
@section('title', 'Issue Book')
@section('content')

<div class="mb-6">
  <a href="{{ route('library.index') }}" class="text-xs text-gray-500">← Library</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">📖 Issue Book</h1>
  <p class="text-sm text-gray-500">Loan period: {{ $settings->loan_days ?? 14 }} days · Max {{ $settings->max_books_per_student ?? 2 }} books per student</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="flex gap-2">
    <input name="adm" value="{{ $adm }}" placeholder="Enter Student ADM number..." required autofocus
           class="flex-1 min-h-[52px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
    <button class="min-h-[52px] px-6 rounded-xl bg-navy text-white font-bold">🔍 Find</button>
  </form>
</div>

@if ($student)
  <div class="bg-white rounded-2xl p-5 shadow mb-4">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-xl">
        {{ strtoupper(substr($student->name, 0, 1)) }}
      </div>
      <div class="flex-1">
        <div class="font-bold text-navy text-lg">{{ $student->name }}</div>
        <div class="text-sm text-gray-600">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow max-w-3xl">
    <form method="POST" action="{{ route('library.issue.store') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="adm" value="{{ $student->adm_no }}">

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">SELECT BOOK *</label>
        <select name="book_id" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          <option value="">-- Select --</option>
          @foreach ($availableBooks as $b)
            <option value="{{ $b->id }}">{{ $b->title }} — {{ $b->author ?? '' }} ({{ $b->available_copies }} left)</option>
          @endforeach
        </select>
      </div>

      <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">📖 Issue Book</button>
    </form>
  </div>
@endif
@endsection
