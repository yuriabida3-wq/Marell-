@extends('layouts.admin')
@section('title', 'Students')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Students</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $students->total() }} total · Page {{ $students->currentPage() }} of {{ $students->lastPage() }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('principal.students.create') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Add Student</a>
    <a href="{{ route('principal.students.import') }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold hover:scale-105 transition">📥 Import CSV</a>
    <a href="{{ route('principal.export-students', ['class' => $class]) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:scale-105 transition">📊 Export CSV</a>
  </div>
</div>

{{-- FILTERS --}}
<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <input name="q" value="{{ $q }}" placeholder="🔍 Search name, ADM, phone..."
           class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">

    <select name="class" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <option value="">All Classes</option>
      @foreach ($classes as $c)
        <option value="{{ $c }}" @selected($class === $c)>{{ $c }}</option>
      @endforeach
    </select>

    <select name="status" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <option value="">All Statuses</option>
      <option value="active"     @selected($status === 'active')>Active</option>
      <option value="suspended"  @selected($status === 'suspended')>Suspended</option>
      <option value="graduated"  @selected($status === 'graduated')>Graduated</option>
    </select>

    <button class="md:col-span-4 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm hover:scale-[1.02] transition">Apply Filters</button>
  </form>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr class="text-left text-xs text-gray-500 tracking-widest">
          <th class="p-3">ADM</th>
          <th class="p-3">NAME</th>
          <th class="p-3">CLASS</th>
          <th class="p-3">PARENT</th>
          <th class="p-3">PHONE</th>
          <th class="p-3 text-right">BALANCE</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($students as $s)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-mono text-xs text-navy">{{ $s->adm_no }}</td>
            <td class="p-3 font-semibold text-navy">{{ $s->name }}</td>
            <td class="p-3 text-gray-600">{{ $s->class }} {{ $s->stream }}</td>
            <td class="p-3 text-gray-600">{{ $s->parent_name }}</td>
            <td class="p-3 text-gray-600 font-mono text-xs">{{ $s->parent_phone }}</td>
            <td class="p-3 text-right font-bold {{ $s->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
              KES {{ number_format($s->balance, 0) }}
            </td>
            <td class="p-3 text-center">
              @php
                $cls = match($s->status) {
                  'active'    => 'bg-green-100 text-green-700',
                  'suspended' => 'bg-red-100 text-red-700',
                  default     => 'bg-gray-100 text-gray-700',
                };
              @endphp
              <span class="px-2 py-1 rounded-full text-xs font-bold {{ $cls }}">{{ ucfirst($s->status) }}</span>
            </td>
            <td class="p-3 text-right">
              <a href="{{ route('principal.students.show', $s) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a>
              <a href="{{ route('principal.students.edit', $s) }}" class="text-xs font-semibold text-navy hover:text-gold ml-2">Edit</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-12 text-center text-gray-400 text-sm">No students match your filters</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- PAGINATION --}}
<div class="mt-4">
  {{ $students->links() }}
</div>

@endsection
