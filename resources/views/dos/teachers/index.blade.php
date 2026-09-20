@extends('layouts.dos')
@section('title', 'Teachers')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Teachers</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $teachers->total() }} registered</p>
  </div>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

  {{-- LIST --}}
  <div class="md:col-span-2 bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-5 border-b">
      <h2 class="font-extrabold text-navy">👨‍🏫 Registered Teachers</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
          <tr class="text-left">
            <th class="p-3">NAME</th>
            <th class="p-3">EMAIL</th>
            <th class="p-3">PHONE</th>
            <th class="p-3 text-center">SLOTS</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($teachers as $t)
            <tr class="hover:bg-gray-50">
              <td class="p-3 font-semibold text-navy">{{ $t->name }}</td>
              <td class="p-3 text-gray-600 text-xs">{{ $t->email }}</td>
              <td class="p-3 text-gray-600 font-mono text-xs">{{ $t->phone ?: '—' }}</td>
              <td class="p-3 text-center font-bold text-navy">{{ $t->timetables_count }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-8 text-center text-gray-400 text-sm">No teachers yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">{{ $teachers->links() }}</div>
  </div>

  {{-- ADD --}}
  <div class="bg-white rounded-2xl p-6 shadow h-fit">
    <h2 class="font-extrabold text-navy mb-4">➕ Register Teacher</h2>
    <form method="POST" action="{{ route('dos.teachers.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NAME *</label>
        <input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EMAIL *</label>
        <input name="email" type="email" value="{{ old('email') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE</label>
        <input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PASSWORD *</label>
        <input name="password" type="password" required minlength="6" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">✅ Register</button>
    </form>
  </div>

</div>

@endsection
