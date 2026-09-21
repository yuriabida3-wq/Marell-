@extends('layouts.public')
@section('title', 'Check Results')
@section('content')
<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">RESULTS</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Check Exam Results</h1>
    <p class="mt-4 text-white/80 max-w-2xl mx-auto">Enter your child's admission number and term to view results instantly.</p>
  </div>
</section>

<section class="max-w-3xl mx-auto px-4 py-12">
  @if (session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-lg">
      <div class="font-bold text-red-700">{{ session('error') }}</div>
    </div>
  @endif

  <div class="card p-6 md:p-8 mb-8" data-aos="fade-up">
    <form method="GET" action="{{ route('results') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-navy mb-2">Admission Number *</label>
        <input name="adm" value="{{ request('adm') }}" required placeholder="e.g. MAR-2024-0001" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>
      <div>
        <label class="block text-sm font-semibold text-navy mb-2">Term</label>
        <select name="term" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
          @foreach (['Term 1','Term 2','Term 3'] as $t)
            <option value="{{ $t }}" @selected(request('term','Term 1')==$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-3">
        <button class="btn btn-gold w-full md:w-auto">🔍 View Results</button>
      </div>
    </form>
  </div>

  @if ($student)
    @if ($student->balance > 5000)
      <div class="card p-6 border-l-4 border-red-600 mb-6" data-aos="fade-up">
        <div class="text-lg font-bold text-red-700 mb-2">⚠️ Outstanding Fee Balance</div>
        <p class="text-sm text-gray-700 mb-4">Your child <strong>{{ $student->name }}</strong> has an outstanding balance of <strong class="text-red-600">KES {{ number_format($student->balance,2) }}</strong>. Clear a minimum of KES 5,000 to view full results.</p>
        <a href="/pay?adm={{ $student->adm_no }}" class="btn btn-gold">💳 Pay Now</a>
      </div>
    @else
      <div class="card p-6 mb-6" data-aos="fade-up">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <div class="text-xs text-gold font-bold tracking-widest">STUDENT</div>
            <div class="text-2xl font-extrabold text-navy">{{ $student->name }}</div>
            <div class="text-sm text-gray-600">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>
          </div>
          <div class="text-right">
            <div class="text-xs text-gray-500">Term</div>
            <div class="font-bold text-navy">{{ request('term','Term 1') }}</div>
          </div>
        </div>
      </div>

      @if ($results->count())
        <div class="card overflow-hidden mb-6" data-aos="fade-up">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-navy text-white">
                <tr><th class="p-3 text-left">Subject</th><th class="p-3 text-center">Marks</th><th class="p-3 text-center">Grade</th></tr>
              </thead>
              <tbody class="divide-y">
                @foreach ($results as $r)
                  <tr class="hover:bg-gray-50">
                    <td class="p-3 font-semibold text-navy">{{ $r->subject }}</td>
                    <td class="p-3 text-center font-bold">{{ $r->marks }}</td>
                    <td class="p-3 text-center"><span class="inline-flex items-center justify-center min-w-[42px] h-8 rounded-full font-bold text-xs
                      @if(in_array($r->grade,['A','A-'])) bg-green-100 text-green-700
                      @elseif(in_array($r->grade,['B+','B','B-'])) bg-blue-100 text-blue-700
                      @elseif(in_array($r->grade,['C+','C','C-'])) bg-yellow-100 text-yellow-700
                      @else bg-red-100 text-red-700 @endif">{{ $r->grade }}</span></td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot class="bg-gray-100">
                <tr><td class="p-3 font-bold text-navy">TOTAL / MEAN</td><td class="p-3 text-center font-bold text-navy">{{ $results->sum('marks') }}</td><td class="p-3 text-center font-bold text-navy">{{ $averageGrade }}</td></tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 justify-center" data-aos="fade-up">
          <a href="#" onclick="window.print(); return false;" class="btn btn-navy">🖨️ Print / Save PDF</a>
          <a href="/pay?adm={{ $student->adm_no }}" class="btn btn-gold">💳 Pay Fees</a>
        </div>
      @else
        <div class="card p-8 text-center" data-aos="fade-up">
          <div class="text-4xl mb-3">📭</div>
          <div class="font-bold text-navy text-lg">No results yet</div>
          <p class="text-sm text-gray-600 mt-2">Results for {{ request('term','Term 1') }} have not been published.</p>
        </div>
      @endif
    @endif
  @endif

  <div class="mt-12 text-center text-sm text-gray-600" data-aos="fade-up">
    <p>Can't find your ADM number? Call <a href="tel:+254700000000" class="text-navy font-semibold">+254 700 000 000</a>.</p>
  </div>
</section>
@include('public._related')
@endsection
