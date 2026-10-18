@extends('layouts.admin')
@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Staff Attendance Log</h1>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-navy text-white">
                <tr>
                    <th class="p-3">Name</th>
                    <th class="p-3">Action</th>
                    <th class="p-3">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr class="border-b">
                    <td class="p-3">{{ $log->user->name ?? 'Unknown' }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $log->action == 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ strtoupper($log->action) }}
                        </span>
                    </td>
                    <td class="p-3">{{ $log->scanned_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
