@extends('layouts.dos')
@section('title', 'Classes')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Classes & Streams</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $classes->total() }} classrooms</p>
  </div>
  <button onclick="document.getElementById('addClassForm').scrollIntoView({behavior:'smooth'})"
          class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Add Class</button>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if ($errors->any())
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
@endif

<div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">CLASS</th>
          <th class="p-3">STREAM</th>
          <th class="p-3">LEVEL</th>
          <th class="p-3">CLASS TEACHER</th>
          <th class="p-3 text-center">CAPACITY</th>
          <th class="p-3 text-center">STUDENTS</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($classes as $c)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-semibold text-navy">{{ $c->name }}</td>
            <td class="p-3">{{ $c->stream ?: '—' }}</td>
            <td class="p-3 text-gray-600">{{ $c->level ?: '—' }}</td>
            <td class="p-3 text-gray-600">{{ $c->classTeacher->name ?? '—' }}</td>
            <td class="p-3 text-center text-gray-600">{{ $c->capacity }}</td>
            <td class="p-3 text-center font-bold text-navy">{{ $c->studentCount() }}</td>
            <td class="p-3 text-center">
              <span class="px-2 py-1 rounded-full text-xs font-bold {{ $c->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                {{ $c->active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-3 text-right whitespace-nowrap">
              <button onclick="openEdit({{ $c->id }}, '{{ $c->name }}', '{{ $c->stream }}', '{{ $c->level }}', '{{ $c->class_teacher_id }}', '{{ $c->capacity }}', {{ $c->active ? 1 : 0 }})"
                      class="text-xs font-semibold text-navy hover:text-gold">Edit</button>
              <form method="POST" action="{{ route('dos.classes.destroy', $c) }}" class="inline" onsubmit="return confirm('Delete this class?');">
                @csrf @method('DELETE')
                <button class="text-xs font-semibold text-red-600 hover:underline ml-2">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-12 text-center text-gray-400 text-sm">No classes yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $classes->links() }}</div>

{{-- ADD CLASS --}}
<div id="addClassForm" class="bg-white rounded-2xl p-6 md:p-8 shadow max-w-3xl">
  <h2 class="font-extrabold text-navy mb-4">➕ Add New Class</h2>
  <form method="POST" action="{{ route('dos.classes.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @csrf
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS NAME *</label>
      <input name="name" required placeholder="e.g. Class 6, Grade 5" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STREAM</label>
      <input name="stream" placeholder="Blue, Green, Red..." class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">LEVEL</label>
      <select name="level" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        <option value="">-- Select --</option>
        @foreach ($levels as $l)
          <option value="{{ $l }}">{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS TEACHER</label>
      <select name="class_teacher_id" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        <option value="">-- None --</option>
        @foreach ($teachers as $t)
          <option value="{{ $t->id }}">{{ $t->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CAPACITY</label>
      <input name="capacity" type="number" value="40" min="1" max="200" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
    </div>
    <button class="md:col-span-2 min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">✅ Create Class</button>
  </form>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-2xl w-full p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="font-extrabold text-navy text-xl">Edit Class</h2>
      <button onclick="closeEdit()" class="text-gray-500 text-2xl leading-none">×</button>
    </div>
    <form id="editForm" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf @method('PUT')
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS NAME *</label>
        <input name="name" id="eName" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STREAM</label>
        <input name="stream" id="eStream" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">LEVEL</label>
        <select name="level" id="eLevel" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="">-- None --</option>
          @foreach ($levels as $l)
            <option value="{{ $l }}">{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CLASS TEACHER</label>
        <select name="class_teacher_id" id="eTeacher" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="">-- None --</option>
          @foreach ($teachers as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CAPACITY</label>
        <input name="capacity" id="eCapacity" type="number" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">STATUS</label>
        <select name="active" id="eActive" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <div class="md:col-span-2 flex gap-3">
        <button class="flex-1 min-h-[48px] rounded-xl bg-gold text-navy font-bold">💾 Save</button>
        <button type="button" onclick="closeEdit()" class="flex-1 min-h-[48px] rounded-xl bg-gray-100 text-gray-700 font-semibold">Cancel</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  function openEdit(id, name, stream, level, teacher, capacity, active) {
    document.getElementById('editForm').action = '/dos/classes/' + id;
    document.getElementById('eName').value = name;
    document.getElementById('eStream').value = stream || '';
    document.getElementById('eLevel').value = level || '';
    document.getElementById('eTeacher').value = teacher || '';
    document.getElementById('eCapacity').value = capacity || '';
    document.getElementById('eActive').value = active ? '1' : '0';
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
  }
  function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
  }
</script>
@endpush

@endsection
