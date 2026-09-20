@extends('layouts.public')
@section('title', 'Admissions')
@section('meta_description', 'Apply online to Marell Academy. Simple 4-step admissions process.')

@section('content')

<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">ADMISSIONS</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Join the Marell Family</h1>
    <p class="mt-4 text-white/80 max-w-2xl mx-auto">Applications for 2025 intake are open. Baby Class through Grade 9.</p>
  </div>
</section>

{{-- CHECKLIST --}}
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="text-center mb-10">
    <div class="text-gold font-bold tracking-widest text-sm">BEFORE YOU APPLY</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Admissions Checklist</h2>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <div class="card p-6" data-aos="fade-up">
      <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center font-bold mb-4">1</div>
      <h3 class="font-bold text-navy mb-2">Required Documents</h3>
      <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
        <li>Birth certificate (copy)</li>
        <li>Previous school report (if any)</li>
        <li>Passport photo (2 copies)</li>
        <li>Parent/Guardian ID (copy)</li>
      </ul>
    </div>
    <div class="card p-6" data-aos="fade-up" data-aos-delay="80">
      <div class="w-12 h-12 rounded-full bg-gold text-navy flex items-center justify-center font-bold mb-4">2</div>
      <h3 class="font-bold text-navy mb-2">Submit Application</h3>
      <p class="text-sm text-gray-700">Fill the form below or visit the school office. Takes 3 minutes online.</p>
    </div>
    <div class="card p-6" data-aos="fade-up" data-aos-delay="160">
      <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center font-bold mb-4">3</div>
      <h3 class="font-bold text-navy mb-2">Interview &amp; Placement</h3>
      <p class="text-sm text-gray-700">We'll call you within 48 hours to schedule an interview and placement test.</p>
    </div>
  </div>

  {{-- FORM --}}
  <div class="max-w-3xl mx-auto">
    <div class="card p-6 md:p-10" data-aos="fade-up">
      <h2 class="text-2xl font-extrabold text-navy mb-6">Application Form</h2>

      @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-6 rounded-lg">
          <div class="font-bold text-green-700">✅ {{ session('success') }}</div>
        </div>
      @endif

      @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg">
          <div class="font-bold text-red-700 mb-2">Please fix the following:</div>
          <ul class="text-sm text-red-600 list-disc list-inside">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('admissions.submit') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf

        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-navy mb-2">Student Full Name *</label>
          <input name="student_name" value="{{ old('student_name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        </div>

        <div>
          <label class="block text-sm font-semibold text-navy mb-2">Date of Birth</label>
          <input name="dob" type="date" value="{{ old('dob') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        </div>

        <div>
          <label class="block text-sm font-semibold text-navy mb-2">Class Applying For *</label>
          <select name="class_applying" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
            <option value="">-- Select --</option>
            @foreach (['Baby Class','PP1','PP2','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9'] as $c)
              <option value="{{ $c }}" @selected(old('class_applying') == $c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-navy mb-2">Parent/Guardian Name *</label>
          <input name="parent_name" value="{{ old('parent_name') }}" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        </div>

        <div>
          <label class="block text-sm font-semibold text-navy mb-2">Parent Phone *</label>
          <input name="parent_phone" value="{{ old('parent_phone') }}" required placeholder="07XXXXXXXX" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-navy mb-2">Parent Email</label>
          <input name="parent_email" type="email" value="{{ old('parent_email') }}" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-navy mb-2">Message (optional)</label>
          <textarea name="message" rows="4" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:border-gold focus:outline-none">{{ old('message') }}</textarea>
        </div>

        <div class="md:col-span-2 flex items-start gap-3">
          <input type="checkbox" name="consent" value="1" required class="mt-1 w-5 h-5">
          <label class="text-sm text-gray-700">I consent to Marell Academy contacting me via phone, SMS, or email regarding this application. *</label>
        </div>

        <div class="md:col-span-2">
          <button type="submit" class="btn btn-gold w-full md:w-auto">📩 Submit Application</button>
        </div>
      </form>
    </div>
  </div>
</section>

<section class="bg-navy text-white py-14">
  <div class="max-w-3xl mx-auto px-4 text-center" data-aos="fade-up">
    <h2 class="text-2xl md:text-3xl font-extrabold mb-3">Need help applying?</h2>
    <p class="text-white/80 mb-6">Call us or visit the school office — we're happy to walk you through.</p>
    <a href="tel:+254700000000" class="btn btn-gold">📞 +254 700 000 000</a>
  </div>
</section>

@endsection
