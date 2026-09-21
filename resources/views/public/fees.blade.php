@extends('layouts.public')
@section('title', 'Fee Structure')
@section('meta_description', 'View Marell Academy fee structure per class, term, and year. Pay online via M-Pesa.')

@section('content')

<section class="bg-navy text-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
    <div class="text-gold font-bold tracking-widest text-sm">FEES</div>
    <h1 class="text-3xl md:text-5xl font-extrabold mt-2">Fee Structure 2025</h1>
    <p class="mt-4 text-white/80 max-w-2xl mx-auto">Transparent, termly fees. Pay online via M-Pesa in seconds.</p>
  </div>
</section>

{{-- FEES TABLE --}}
<section class="max-w-7xl mx-auto px-4 py-12">
  @php
    $fees = [
      ['Baby Class / PP1 / PP2', 12000, 12000, 12000],
      ['Grade 1',                15000, 15000, 15000],
      ['Grade 2',                15000, 15000, 15000],
      ['Grade 3',                15500, 15500, 15500],
      ['Grade 4',                17000, 17000, 17000],
      ['Grade 5',                18000, 18000, 18000],
      ['Grade 6',                18500, 18500, 18500],
      ['Grade 7 (JSS)',          22000, 22000, 22000],
      ['Grade 8 (JSS)',          23000, 23000, 23000],
      ['Grade 9 (JSS)',          24000, 24000, 24000],
    ];
  @endphp

  <div class="card overflow-hidden" data-aos="fade-up">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-navy text-white">
          <tr>
            <th class="p-3 text-left">Class</th>
            <th class="p-3 text-right">Term 1</th>
            <th class="p-3 text-right">Term 2</th>
            <th class="p-3 text-right">Term 3</th>
            <th class="p-3 text-right">Annual Total</th>
            <th class="p-3 text-center">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach ($fees as $f)
            @php $total = $f[1] + $f[2] + $f[3]; @endphp
            <tr class="hover:bg-gray-50">
              <td class="p-3 font-semibold text-navy">{{ $f[0] }}</td>
              <td class="p-3 text-right">KES {{ number_format($f[1]) }}</td>
              <td class="p-3 text-right">KES {{ number_format($f[2]) }}</td>
              <td class="p-3 text-right">KES {{ number_format($f[3]) }}</td>
              <td class="p-3 text-right font-bold text-navy">KES {{ number_format($total) }}</td>
              <td class="p-3 text-center">
                <a href="/pay?class={{ urlencode($f[0]) }}&amount={{ $f[1] }}"
                   class="inline-flex items-center justify-center min-h-[40px] px-4 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 hover:scale-105 transition">
                  Pay Now
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center" data-aos="fade-up">
    <a href="#" onclick="window.print(); return false;" class="btn btn-navy">🖨️ Print / Save PDF</a>
    <a href="/pay" class="btn btn-gold">💳 Pay Fees Online</a>
  </div>
</section>

{{-- HOW TO PAY --}}
<section class="bg-gray-100 py-16">
  <div class="max-w-4xl mx-auto px-4">
    <div class="text-center mb-10">
      <div class="text-gold font-bold tracking-widest text-sm">EASY PAYMENT</div>
      <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">How to Pay</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="card p-6" data-aos="fade-up">
        <div class="text-3xl mb-3">📱</div>
        <h3 class="font-bold text-navy text-lg mb-3">Pay via M-Pesa (STK Push)</h3>
        <ol class="text-sm text-gray-700 space-y-2 list-decimal list-inside">
          <li>Visit <span class="font-semibold">marell.ac.ke/pay</span></li>
          <li>Enter your child's ADM number</li>
          <li>Enter amount and tap <span class="font-semibold">Pay via M-Pesa</span></li>
          <li>Approve the prompt on your phone</li>
          <li>Receive instant receipt via SMS</li>
        </ol>
      </div>

      <div class="card p-6" data-aos="fade-up" data-aos-delay="100">
        <div class="text-3xl mb-3">🏦</div>
        <h3 class="font-bold text-navy text-lg mb-3">Pay via Bank / Paybill</h3>
        <div class="text-sm text-gray-700 space-y-2">
          <div><span class="font-semibold">Paybill Number:</span> 247247</div>
          <div><span class="font-semibold">Account:</span> Your child's ADM number (e.g. MAR-2024-0001)</div>
          <div class="pt-2 border-t mt-2">
            <div class="font-semibold text-navy">Bank Transfer</div>
            <div>Bank: Equity Bank Kenya</div>
            <div>Account Name: Marell Academy Ltd</div>
            <div>Account No: 0123456789012</div>
            <div>Branch: Bungoma</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="max-w-4xl mx-auto px-4 py-16">
  <div class="text-center mb-10">
    <div class="text-gold font-bold tracking-widest text-sm">FREQUENTLY ASKED</div>
    <h2 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Fee FAQs</h2>
  </div>

  @php
    $faqs = [
      ['Can I pay fees in installments?', 'Yes. Fees can be paid in any number of installments across the term. A minimum of 50% is required by mid-term.'],
      ['Do you accept M-Pesa?', 'Yes — M-Pesa is our preferred method. Paybill 247247 or via STK Push on our website.'],
      ['Are there hidden charges?', 'No. The fee structure above is all-inclusive of tuition, exams, and basic learning materials. Lunch and boarding are billed separately.'],
      ['What happens if I pay late?', 'A late payment notice is sent via SMS. Continuous default may lead to suspension until cleared.'],
      ['Can I get a fee statement?', 'Yes. Log into the Parent Portal with your phone number to view or download your statement.'],
    ];
  @endphp

  <div class="space-y-3">
    @foreach ($faqs as $i => $faq)
      <details class="card p-5 cursor-pointer group" data-aos="fade-up">
        <summary class="font-semibold text-navy flex items-center justify-between list-none">
          <span>{{ $faq[0] }}</span>
          <span class="text-gold text-2xl group-open:rotate-45 transition">+</span>
        </summary>
        <p class="mt-3 text-sm text-gray-700">{{ $faq[1] }}</p>
      </details>
    @endforeach
  </div>
</section>

@include('public._related')
@endsection
