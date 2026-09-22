@extends('layouts.app')
@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Staff QR Codes</h1>
    <p class="text-gray-600 mb-6">Print these and give one to each staff member. They show this to the guard at the gate.</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($staff as $member)
        <div class="bg-white p-4 rounded-xl shadow text-center border">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $member->email }}" alt="QR Code" class="mx-auto mb-2 rounded">
            <p class="font-bold">{{ $member->name }}</p>
            <p class="text-xs text-gray-500">{{ $member->email }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
