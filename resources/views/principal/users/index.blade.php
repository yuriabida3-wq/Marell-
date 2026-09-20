@extends('layouts.admin')
@section('title', 'Users')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
  <div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Users</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $users->total() }} staff members</p>
  </div>
  <a href="{{ route('principal.users.create') }}" class="px-4 py-2 rounded-xl bg-gold text-navy font-bold text-sm hover:scale-105 transition">+ Add User</a>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="bg-white rounded-2xl p-4 shadow mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <input name="q" value="{{ $q }}" placeholder="🔍 Name, email, phone..."
           class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
    <select name="role" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-4 text-sm focus:border-gold focus:outline-none">
      <option value="">All Roles</option>
      @foreach ($roles as $r)
        <option value="{{ $r }}" @selected($role === $r)>{{ ucfirst($r) }}</option>
      @endforeach
    </select>
    <button class="md:col-span-3 min-h-[42px] rounded-xl bg-navy text-white font-semibold text-sm hover:scale-[1.02] transition">Apply</button>
  </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">NAME</th>
          <th class="p-3">EMAIL</th>
          <th class="p-3">PHONE</th>
          <th class="p-3">ROLE</th>
          <th class="p-3 text-center">STATUS</th>
          <th class="p-3 text-right">ACTION</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($users as $u)
          <tr class="hover:bg-gray-50">
            <td class="p-3 font-semibold text-navy">{{ $u->name }}</td>
            <td class="p-3 text-gray-600 text-xs">{{ $u->email }}</td>
            <td class="p-3 text-gray-600 font-mono text-xs">{{ $u->phone ?: '—' }}</td>
            <td class="p-3">
              @foreach ($u->getRoleNames() as $r)
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-navy/10 text-navy">{{ $r }}</span>
              @endforeach
            </td>
            <td class="p-3 text-center">
              @if ($u->active)
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
              @else
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Inactive</span>
              @endif
            </td>
            <td class="p-3 text-right whitespace-nowrap">
              <a href="{{ route('principal.users.edit', $u) }}" class="text-xs font-semibold text-navy hover:text-gold">Edit</a>
              <form method="POST" action="{{ route('principal.users.toggle', $u) }}" class="inline">
                @csrf
                <button class="text-xs font-semibold {{ $u->active ? 'text-red-600' : 'text-green-600' }} hover:underline ml-2">
                  {{ $u->active ? 'Deactivate' : 'Activate' }}
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-12 text-center text-gray-400 text-sm">No users</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $users->links() }}</div>

@endsection
