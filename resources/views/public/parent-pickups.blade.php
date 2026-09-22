@extends('layouts.public')
@section('title', 'Approved Pickups')
@section('content')

<section class="bg-navy text-white py-10">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="text-gold font-bold tracking-widest text-xs">PARENT PORTAL</div>
        <h1 class="text-2xl md:text-3xl font-extrabold mt-1">Approved Pickups</h1>
        <p class="text-white/70 text-sm mt-1">Manage who can pick up your child from school</p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('parent.dashboard') }}" class="btn btn-outline text-sm !min-h-[42px]">← Dashboard</a>
      </div>
    </div>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-8">

  @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
  @endif
  @if (session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
  @endif

  {{-- CHILD SWITCHER --}}
  @if ($children->count() > 1)
    <div class="mb-6">
      <div class="text-xs text-gray-500 font-semibold tracking-widest mb-2">SWITCH CHILD</div>
      <div class="flex flex-wrap gap-2">
        @foreach ($children as $c)
          <a href="{{ route('parent.pickups', ['child' => $c->id]) }}"
             class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $c->id === $selected->id ? 'bg-navy text-white' : 'bg-gray-200 text-navy hover:bg-gray-300' }}">
            {{ $c->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- CHILD CARD --}}
  <div class="card p-5 mb-6">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-gold flex items-center justify-center font-bold text-navy text-xl">
        {{ strtoupper(substr($selected->name, 0, 1)) }}
      </div>
      <div>
        <div class="font-extrabold text-navy text-lg">{{ $selected->name }}</div>
        <div class="text-sm text-gray-600">{{ $selected->adm_no }} · {{ $selected->class }} {{ $selected->stream }}</div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ADD NEW --}}
    <div class="lg:col-span-1">
      <div class="card p-6 sticky top-20">
        <h2 class="font-extrabold text-navy mb-4">➕ Add Approved Person</h2>

        <div class="bg-gold/10 border-l-4 border-gold rounded p-3 mb-4 text-xs text-navy">
          Only people listed here will be allowed to pick up <strong>{{ $selected->name }}</strong>. Each gets a QR code you can share.
        </div>

        <form method="POST" action="{{ route('parent.pickups.store') }}" class="space-y-3">
          @csrf
          <input type="hidden" name="student_id" value="{{ $selected->id }}">

          <div>
            <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">FULL NAME *</label>
            <input name="name" value="{{ old('name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          </div>
          <div>
            <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE *</label>
            <input name="phone" value="{{ old('phone') }}" required placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          </div>
          <div>
            <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">RELATIONSHIP *</label>
            <select name="relationship" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
              <option value="">-- Select --</option>
              @foreach (['Mother','Father','Guardian','Uncle','Aunt','Grandmother','Grandfather','Brother','Sister','Driver','Househelp','Other'] as $rel)
                <option value="{{ $rel }}">{{ $rel }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">ID NUMBER</label>
            <input name="id_number" value="{{ old('id_number') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          </div>

          <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">Add & Generate QR</button>
        </form>
      </div>
    </div>

    {{-- LIST --}}
    <div class="lg:col-span-2 space-y-3">
      <h2 class="font-extrabold text-navy mb-2">Approved Pickups ({{ $pickups->count() }})</h2>

      @forelse ($pickups as $p)
        <div class="card p-4 {{ !$p->active ? 'opacity-60' : '' }}">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full {{ $p->active ? 'bg-navy' : 'bg-gray-400' }} text-white flex items-center justify-center font-bold text-lg flex-shrink-0">
              {{ strtoupper(substr($p->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <div class="font-bold text-navy">{{ $p->name }}</div>
                @if (!$p->active)
                  <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">INACTIVE</span>
                @endif
              </div>
              <div class="text-xs text-gray-500 mt-0.5">{{ $p->relationship }} · {{ $p->phone }}</div>
              @if ($p->id_number)
                <div class="text-xs text-gray-400 mt-0.5">ID: {{ $p->id_number }}</div>
              @endif
            </div>
          </div>

          <div class="flex flex-wrap gap-2 mt-3">
            <a href="{{ route('parent.pickups.qr', $p) }}" class="px-3 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold">📱 Show QR</a>

            <form method="POST" action="{{ route('parent.pickups.toggle', $p) }}" class="inline">
              @csrf
              <button class="px-3 py-1.5 rounded-lg bg-gray-100 text-navy text-xs font-semibold">
                {{ $p->active ? '🚫 Deactivate' : '✅ Activate' }}
              </button>
            </form>

            <form method="POST" action="{{ route('parent.pickups.destroy', $p) }}" class="inline" onsubmit="return confirm('Remove {{ $p->name }}?')">
              @csrf @method('DELETE')
              <button class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold">Delete</button>
            </form>
          </div>
        </div>
      @empty
        <div class="card p-12 text-center text-gray-400">
          <div class="text-4xl mb-2">👥</div>
          No approved pickups yet. Add the first person on the left.
        </div>
      @endforelse
    </div>
  </div>
</section>
@endsection
