@extends('layouts.public')
@section('title', 'Report Anonymously')
@section('content')
<section class="bg-navy text-white py-12">
  <div class="max-w-3xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-sm">SPEAK SAFELY</div>
    <h1 class="text-3xl font-extrabold mt-2">Anonymous Report Box</h1>
    <p class="text-white/80 mt-2 text-sm">Your message goes <strong>directly and only to the Director.</strong> No names, no tracking.</p>
  </div>
</section>
<section class="max-w-2xl mx-auto px-4 py-12">
  @if (session('success'))
    <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-6"><div class="font-bold text-green-700">{{ session('success') }}</div></div>
  @endif
  @if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-6"><ul class="text-sm text-red-700 list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
  @endif
  <div class="card p-6 md:p-8">
    <div class="bg-gold/10 border border-gold rounded-xl p-4 mb-6 text-sm text-navy">
      Fully anonymous. We do not store your IP, phone, or email. If you want a reply, optionally add contact info below.
    </div>
    <form method="POST" action="{{ route('confession.submit') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">WHAT IS THIS ABOUT? *</label>
        <select name="category" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
          <option value="">-- Select --</option>
          <option value="bursar">Bursar / Finance</option>
          <option value="teacher">Teacher / Class</option>
          <option value="staff">Staff Conduct</option>
          <option value="facility">Facilities / Environment</option>
          <option value="bullying">Bullying / Safety</option>
          <option value="fees">Fees / Charges</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">YOUR MESSAGE *</label>
        <textarea name="message" rows="6" required minlength="10" maxlength="2000" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3" placeholder="Describe what happened...">{{ old('message') }}</textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CONTACT (optional)</label>
        <input name="contact" value="{{ old('contact') }}" placeholder="Phone or email (optional)" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4">
      </div>
      <button class="w-full min-h-[52px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">Send Anonymously</button>
    </form>
  </div>
</section>
@include('public._related')
@endsection
