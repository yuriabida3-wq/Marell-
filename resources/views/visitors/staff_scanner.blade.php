<div class="mt-4 p-4 bg-white rounded-xl shadow-sm border border-gray-200">
    <h3 class="font-bold text-navy mb-2">Staff Attendance Scanner</h3>
    <form action="{{ route('gate.console.staff-scan') }}" method="POST" class="flex gap-2">
        @csrf
        <input type="text" name="qr_data" placeholder="Scan Staff QR here..." class="flex-1 border rounded-xl p-2" autofocus>
        <button type="submit" class="bg-navy text-white px-4 py-2 rounded-xl font-bold">Log Staff</button>
    </form>
    @if(session('success')) <p class="text-green-600 text-sm mt-2">{{ session('success') }}</p> @endif
    @if(session('error')) <p class="text-red-600 text-sm mt-2">{{ session('error') }}</p> @endif
</div>
