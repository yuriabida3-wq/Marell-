@extends('layouts.admin')
@section('title', $contact->name)
@section('content')
<div class="mb-6"><a href="{{ route('principal.contacts.index') }}" class="text-xs text-gray-500">← Messages</a><h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">{{ $contact->subject ?: 'Message from ' . $contact->name }}</h1></div>
<div class="bg-white rounded-2xl p-6 shadow max-w-3xl">
  <table class="w-full text-sm mb-4">
    <tr class="border-b"><td class="py-2 text-gray-500">From</td><td class="py-2 text-right font-semibold">{{ $contact->name }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Email</td><td class="py-2 text-right">{{ $contact->email ?: '—' }}</td></tr>
    <tr class="border-b"><td class="py-2 text-gray-500">Phone</td><td class="py-2 text-right font-mono">{{ $contact->phone ?: '—' }}</td></tr>
    <tr><td class="py-2 text-gray-500">Date</td><td class="py-2 text-right text-xs">{{ $contact->created_at->format('d M Y H:i') }}</td></tr>
  </table>
  <div class="bg-gray-50 rounded-xl p-4 text-sm whitespace-pre-wrap">{{ $contact->message }}</div>
  <div class="mt-4 flex flex-wrap gap-2">
    @if ($contact->email)<a href="mailto:{{ $contact->email }}" class="px-4 py-2 rounded-xl bg-navy text-white text-sm font-semibold">✉️ Reply Email</a>@endif
    @if ($contact->phone)<a href="tel:{{ $contact->phone }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold">📞 Call</a>@endif
    <form method="POST" action="{{ route('principal.contacts.toggle', $contact) }}" class="contents">@csrf<button class="px-4 py-2 rounded-xl bg-gray-200 text-navy text-sm font-semibold">{{ $contact->handled ? 'Mark Unread' : 'Mark Handled' }}</button></form>
  </div>
</div>
@endsection
