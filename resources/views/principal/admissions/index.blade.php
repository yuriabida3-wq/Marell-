@extends('layouts.admin')
@section('title', 'Applications')
@section('content')
<div class="mb-6"><h1 class="text-2xl md:text-3xl font-extrabold text-navy">Admission Applications</h1></div>
@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>@endif
<div class="grid grid-cols-4 gap-3 mb-4">
  <a href="?status=new" class="bg-white rounded-2xl p-4 shadow text-center {{ $status==='new'?'ring-2 ring-gold':'' }}"><div class="text-[10px] text-gray-500 tracking-widest font-bold">NEW</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $counts['new'] }}</div></a>
  <a href="?status=contacted" class="bg-white rounded-2xl p-4 shadow text-center {{ $status==='contacted'?'ring-2 ring-gold':'' }}"><div class="text-[10px] text-gray-500 tracking-widest font-bold">CONTACTED</div><div class="text-2xl font-extrabold text-navy mt-1">{{ $counts['contacted'] }}</div></a>
  <a href="?status=admitted" class="bg-white rounded-2xl p-4 shadow text-center {{ $status==='admitted'?'ring-2 ring-gold':'' }}"><div class="text-[10px] text-gray-500 tracking-widest font-bold">ADMITTED</div><div class="text-2xl font-extrabold text-green-700 mt-1">{{ $counts['admitted'] }}</div></a>
  <a href="?status=rejected" class="bg-white rounded-2xl p-4 shadow text-center {{ $status==='rejected'?'ring-2 ring-gold':'' }}"><div class="text-[10px] text-gray-500 tracking-widest font-bold">REJECTED</div><div class="text-2xl font-extrabold text-red-600 mt-1">{{ $counts['rejected'] }}</div></a>
</div>
<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500"><tr class="text-left"><th class="p-3">DATE</th><th class="p-3">STUDENT</th><th class="p-3">CLASS</th><th class="p-3">PARENT</th><th class="p-3">PHONE</th><th class="p-3 text-center">STATUS</th><th class="p-3 text-right">ACTION</th></tr></thead>
    <tbody class="divide-y">
      @forelse ($applications as $a)
        <tr class="hover:bg-gray-50">
          <td class="p-3 text-xs text-gray-500">{{ $a->created_at->format('d M') }}</td>
          <td class="p-3 font-semibold text-navy">{{ $a->student_name }}</td>
          <td class="p-3 text-gray-600">{{ $a->class_applying }}</td>
          <td class="p-3 text-gray-600">{{ $a->parent_name }}</td>
          <td class="p-3 text-gray-600 font-mono text-xs">{{ $a->parent_phone }}</td>
          <td class="p-3 text-center"><span class="px-2 py-1 rounded-full text-xs font-bold @if($a->status==='new') bg-blue-100 text-blue-700 @elseif($a->status==='contacted') bg-yellow-100 text-yellow-700 @elseif($a->status==='admitted') bg-green-100 text-green-700 @else bg-red-100 text-red-700 @endif">{{ ucfirst($a->status) }}</span></td>
          <td class="p-3 text-right"><a href="{{ route('principal.admissions.show', $a) }}" class="text-xs font-semibold text-navy hover:text-gold">View</a></td>
        </tr>
      @empty
        <tr><td colspan="7" class="p-12 text-center text-gray-400">No applications</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection
