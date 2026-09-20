@extends('layouts.admin')
@section('title', $admission->student_name)
@section('content')
<div class="mb-6"><a href="{{ route('principal.admissions.index') }}" class="text-xs text-gray-500">← Applications</a><h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $admission->student_name }}</h1></div>
@if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>@endif
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="md:col-span-2 bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">Application Details</h2>
    <table class="w-full text-sm">
      <tr class="border-b"><td class="py-2 text-gray-500">Student</td><td class="py-2 font-semibold text-navy text-right">{{ $admission->student_name }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">DOB</td><td class="py-2 text-right">{{ $admission->dob ? $admission->dob->format('d M Y') : '—' }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Class Applying</td><td class="py-2 text-right">{{ $admission->class_applying }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Parent</td><td class="py-2 text-right">{{ $admission->parent_name }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Phone</td><td class="py-2 text-right font-mono">{{ $admission->parent_phone }}</td></tr>
      <tr class="border-b"><td class="py-2 text-gray-500">Email</td><td class="py-2 text-right">{{ $admission->parent_email ?: '—' }}</td></tr>
      <tr><td class="py-2 text-gray-500 align-top">Message</td><td class="py-2 text-right">{{ $admission->message ?: '—' }}</td></tr>
    </table>
    <div class="mt-6 flex flex-wrap gap-2">
      <a href="tel:{{ $admission->parent_phone }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">📞 Call</a>
      <a href="https://wa.me/{{ preg_replace('/\D/','',$admission->parent_phone) }}" target="_blank" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold">💬 WhatsApp</a>
      <a href="sms:{{ $admission->parent_phone }}" class="px-4 py-2 rounded-xl bg-gray-200 text-navy text-sm font-semibold">📱 SMS</a>
    </div>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow h-fit">
    <h2 class="font-extrabold text-navy mb-4">Status</h2>
    <form method="POST" action="{{ route('principal.admissions.status', $admission) }}" class="space-y-3">
      @csrf
      <select name="status" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
        @foreach (['new','contacted','admitted','rejected'] as $s)
          <option value="{{ $s }}" @selected($admission->status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">Update Status</button>
    </form>
    <form method="POST" action="{{ route('principal.admissions.destroy', $admission) }}" class="mt-3" onsubmit="return confirm('Delete this application?')">
      @csrf @method('DELETE')
      <button class="w-full min-h-[42px] rounded-xl bg-red-50 text-red-600 text-sm font-semibold">Delete</button>
    </form>
  </div>
</div>
@endsection
