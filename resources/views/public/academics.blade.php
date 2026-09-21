@extends('layouts.public')
@section('title', 'Academics')
@section('meta_description', 'CBC-aligned curriculum from Baby Class to Junior Secondary. View our subjects, timetable, and KCSE performance.')

@section('content')

{{-- HERO --}}
<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">ACADEMICS</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Excellence at Every Level</h1>
    <p class="mt-4 text-white/80 max-w-2xl mx-auto">A seamless CBC journey from Baby Class through Junior Secondary — designed for depth, curiosity, and mastery.</p>
  </div>
</section>

{{-- TABS --}}
<section class="max-w-7xl mx-auto px-4 py-12">
  <div class="flex flex-wrap gap-2 justify-center mb-8" data-aos="fade-up">
    <button onclick="showTab('baby')" id="tab-baby" class="tab-btn btn btn-navy text-sm !min-h-[42px]">Baby &amp; PP1 · PP2</button>
    <button onclick="showTab('lower')" id="tab-lower" class="tab-btn btn bg-gray-200 text-navy text-sm !min-h-[42px]">Lower Primary (1–3)</button>
    <button onclick="showTab('upper')" id="tab-upper" class="tab-btn btn bg-gray-200 text-navy text-sm !min-h-[42px]">Upper Primary (4–6)</button>
    <button onclick="showTab('junior')" id="tab-junior" class="tab-btn btn bg-gray-200 text-navy text-sm !min-h-[42px]">Junior Secondary (7–9)</button>
  </div>

  {{-- BABY --}}
  <div id="panel-baby" class="tab-panel">
    <div class="card p-6 md:p-8" data-aos="fade-up">
      <h3 class="text-xl md:text-2xl font-bold text-navy mb-4">Baby Class · PP1 · PP2</h3>
      <p class="text-gray-600 mb-6">A play-based, CBC-aligned foundation that sparks curiosity and builds early literacy, numeracy, and social skills.</p>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-navy text-white">
            <tr><th class="p-3 text-left">Learning Area</th><th class="p-3 text-left">Focus</th></tr>
          </thead>
          <tbody class="divide-y">
            <tr><td class="p-3 font-semibold">Language Activities</td><td class="p-3">Listening, speaking, pre-reading, pre-writing</td></tr>
            <tr><td class="p-3 font-semibold">Mathematical Activities</td><td class="p-3">Number recognition, sorting, patterns, shapes</td></tr>
            <tr><td class="p-3 font-semibold">Environmental Activities</td><td class="p-3">My body, my family, my school, weather</td></tr>
            <tr><td class="p-3 font-semibold">Psychomotor &amp; Creative</td><td class="p-3">Music, art, movement, outdoor play</td></tr>
            <tr><td class="p-3 font-semibold">Religious Education</td><td class="p-3">Stories, songs, moral values</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- LOWER --}}
  <div id="panel-lower" class="tab-panel hidden">
    <div class="card p-6 md:p-8" data-aos="fade-up">
      <h3 class="text-xl md:text-2xl font-bold text-navy mb-4">Lower Primary · Grade 1–3</h3>
      <p class="text-gray-600 mb-6">Building strong literacy and numeracy foundations, plus environmental and creative exploration.</p>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-navy text-white">
            <tr><th class="p-3 text-left">Subject</th><th class="p-3 text-left">Lessons/Week</th></tr>
          </thead>
          <tbody class="divide-y">
            <tr><td class="p-3 font-semibold">English</td><td class="p-3">5</td></tr>
            <tr><td class="p-3 font-semibold">Kiswahili</td><td class="p-3">4</td></tr>
            <tr><td class="p-3 font-semibold">Mathematics</td><td class="p-3">5</td></tr>
            <tr><td class="p-3 font-semibold">Environmental Activities</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Hygiene &amp; Nutrition</td><td class="p-3">2</td></tr>
            <tr><td class="p-3 font-semibold">Religious Education (CRE/IRE)</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Creative Arts</td><td class="p-3">2</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- UPPER --}}
  <div id="panel-upper" class="tab-panel hidden">
    <div class="card p-6 md:p-8" data-aos="fade-up">
      <h3 class="text-xl md:text-2xl font-bold text-navy mb-4">Upper Primary · Grade 4–6</h3>
      <p class="text-gray-600 mb-6">Deeper subject matter, critical thinking, and introduction to specialized sciences and technology.</p>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-navy text-white">
            <tr><th class="p-3 text-left">Subject</th><th class="p-3 text-left">Lessons/Week</th></tr>
          </thead>
          <tbody class="divide-y">
            <tr><td class="p-3 font-semibold">English</td><td class="p-3">5</td></tr>
            <tr><td class="p-3 font-semibold">Kiswahili</td><td class="p-3">4</td></tr>
            <tr><td class="p-3 font-semibold">Mathematics</td><td class="p-3">6</td></tr>
            <tr><td class="p-3 font-semibold">Science &amp; Technology</td><td class="p-3">4</td></tr>
            <tr><td class="p-3 font-semibold">Social Studies</td><td class="p-3">4</td></tr>
            <tr><td class="p-3 font-semibold">Religious Education</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Agriculture</td><td class="p-3">2</td></tr>
            <tr><td class="p-3 font-semibold">Creative Arts</td><td class="p-3">2</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- JUNIOR --}}
  <div id="panel-junior" class="tab-panel hidden">
    <div class="card p-6 md:p-8" data-aos="fade-up">
      <h3 class="text-xl md:text-2xl font-bold text-navy mb-4">Junior Secondary · Grade 7–9</h3>
      <p class="text-gray-600 mb-6">Specialized pathways preparing learners for Senior School and beyond.</p>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-navy text-white">
            <tr><th class="p-3 text-left">Subject</th><th class="p-3 text-left">Lessons/Week</th></tr>
          </thead>
          <tbody class="divide-y">
            <tr><td class="p-3 font-semibold">English</td><td class="p-3">5</td></tr>
            <tr><td class="p-3 font-semibold">Kiswahili</td><td class="p-3">4</td></tr>
            <tr><td class="p-3 font-semibold">Mathematics</td><td class="p-3">6</td></tr>
            <tr><td class="p-3 font-semibold">Integrated Science</td><td class="p-3">5</td></tr>
            <tr><td class="p-3 font-semibold">Pre-Technical Studies</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Social Studies</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Religious Education</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Agriculture</td><td class="p-3">3</td></tr>
            <tr><td class="p-3 font-semibold">Business Studies</td><td class="p-3">2</td></tr>
            <tr><td class="p-3 font-semibold">ICT</td><td class="p-3">2</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

{{-- KCSE PERFORMANCE --}}
<section class="bg-gray-100 py-16">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-10">
      <div class="text-gold font-bold tracking-widest text-sm">PROVEN TRACK RECORD</div>
      <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">KCSE Performance</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="card p-6 text-center" data-aos="fade-up">
        <div class="text-4xl font-extrabold text-navy">8.2</div>
        <div class="text-sm text-gray-600 mt-2">2024 Mean Grade</div>
      </div>
      <div class="card p-6 text-center" data-aos="fade-up" data-aos-delay="80">
        <div class="text-4xl font-extrabold text-gold">98%</div>
        <div class="text-sm text-gray-600 mt-2">University Qualifying</div>
      </div>
      <div class="card p-6 text-center" data-aos="fade-up" data-aos-delay="160">
        <div class="text-4xl font-extrabold text-navy">120+</div>
        <div class="text-sm text-gray-600 mt-2">A &amp; A- Grades</div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="/timetable" class="btn btn-navy">📅 View Timetable</a>
      <a href="/fees" class="btn btn-gold">💰 Download Fee Structure</a>
    </div>
  </div>
</section>

@push('scripts')
<script>
function showTab(key){
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
  document.getElementById('panel-' + key).classList.remove('hidden');

  document.querySelectorAll('.tab-btn').forEach(b => {
    b.classList.remove('btn-navy');
    b.classList.add('bg-gray-200','text-navy');
  });
  const active = document.getElementById('tab-' + key);
  active.classList.remove('bg-gray-200','text-navy');
  active.classList.add('btn-navy');
}
</script>
@endpush

@include('public._related')
@endsection
