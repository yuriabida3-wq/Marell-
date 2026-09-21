@extends(role_layout())
@section('title', 'Gate')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">🚪 Gate Control</h1>
    <p class="text-sm text-gray-500 mt-1">Live visitor sign-in / sign-out</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('visitors.check-in') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Check In Visitor</a>
    <a href="{{ route('visitors.index') }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">📋 All Logs</a>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-navy rounded-2xl p-5 text-white shadow">
    <div class="text-[10px] tracking-widest font-bold text-gold">CURRENTLY INSIDE</div>
    <div class="text-3xl font-extrabold mt-1">{{ $inside->count() }}</div>
  </div>
  <div class="bg-gold rounded-2xl p-5 text-navy shadow">
    <div class="text-[10px] tracking-widest font-bold">EXPECTED</div>
    <div class="text-3xl font-extrabold mt-1">{{ $expected->count() }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-navy">CHECKED IN TODAY</div>
    <div class="text-3xl font-extrabold text-navy mt-1">{{ $todayIn }}</div>
  </div>
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="text-[10px] tracking-widest font-bold text-gray-500">CHECKED OUT TODAY</div>
    <div class="text-3xl font-extrabold text-gray-700 mt-1">{{ $todayOut }}</div>
  </div>
</div>

{{-- CURRENTLY INSIDE --}}
<div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
  <div class="p-5 border-b bg-green-50">
    <div class="font-extrabold text-navy text-lg">✅ Currently Inside ({{ $inside->count() }})</div>
  </div>
  @if ($inside->count())
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">BADGE</th>
          <th class="p-3">NAME</th>
          <th class="p-3">PURPOSE</th>
          <th class="p-3">HOST</th>
          <th class="p-3">IN AT</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($inside as $v)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-mono font-bold text-navy">{{ $v->badge_no }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy">{{ $v->name }}</div>
              <div class="text-xs text-gray-500 font-mono">{{ $v->phone }}</div>
            </td>
            <td class="p-3 text-gray-700">{{ $v->purpose }}</td>
            <td class="p-3 text-xs text-gray-600">
              @if ($v->host_name) {{ $v->host_name }}@endif
              @if ($v->student_adm) <div class="text-xs">Visiting: {{ $v->student_adm }}</div>@endif
            </td>
            <td class="p-3 text-xs text-gray-500">{{ $v->checked_in_at->format('H:i') }}</td>
            <td class="p-3 text-right">
              <form method="POST" action="{{ route('visitors.check-out', $v) }}" class="inline">
                @csrf
                <button class="text-xs font-semibold text-red-600 hover:underline">Check Out</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="p-12 text-center text-gray-400">
      <div class="text-4xl mb-2">🕊️</div>
      No visitors inside right now
    </div>
  @endif
</div>

{{-- EXPECTED --}}
@if ($expected->count())
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b bg-yellow-50">
      <div class="font-extrabold text-navy text-lg">⏳ Expected Today ({{ $expected->count() }})</div>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">NAME</th>
          <th class="p-3">PHONE</th>
          <th class="p-3">PURPOSE</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach ($expected as $v)
          <tr>
            <td class="p-3 font-semibold text-navy">{{ $v->name }}</td>
            <td class="p-3 font-mono text-xs">{{ $v->phone }}</td>
            <td class="p-3 text-gray-700 text-xs">{{ $v->purpose }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
@endsection
