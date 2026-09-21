@extends('layouts.public')
@section('title', 'Verify Receipt')
@section('content')
<section class="bg-navy text-white py-12">
  <div class="max-w-3xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-sm">OFFICIAL VERIFICATION</div>
    <h1 class="text-3xl font-extrabold mt-2">Receipt Verification</h1>
    <p class="text-white/80 mt-2 text-sm">Receipt No: <span class="font-mono font-bold">{{ $receiptNo }}</span></p>
  </div>
</section>

<section class="max-w-3xl mx-auto px-4 py-12">
  @if ($payment)
    <div class="card p-8 border-t-4 border-green-600">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
          <svg class="w-9 h-9 text-green-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
          <div class="text-2xl font-extrabold text-green-700">VERIFIED</div>
          <div class="text-sm text-gray-500">This is a genuine Marell Academy receipt</div>
        </div>
      </div>

      <table class="w-full text-sm">
        <tr class="border-b"><td class="py-2 text-gray-500">Receipt No</td><td class="py-2 text-right font-mono font-bold text-navy">{{ $payment->receipt_no }}</td></tr>
        <tr class="border-b"><td class="py-2 text-gray-500">Student</td><td class="py-2 text-right font-semibold">{{ $payment->student->name ?? '—' }}</td></tr>
        <tr class="border-b"><td class="py-2 text-gray-500">ADM No</td><td class="py-2 text-right font-mono">{{ $payment->student->adm_no ?? '—' }}</td></tr>
        <tr class="border-b"><td class="py-2 text-gray-500">Amount</td><td class="py-2 text-right font-bold text-green-700 text-lg">KES {{ number_format($payment->amount, 2) }}</td></tr>
        <tr class="border-b"><td class="py-2 text-gray-500">Method</td><td class="py-2 text-right">{{ $payment->method }}</td></tr>
        <tr class="border-b"><td class="py-2 text-gray-500">Transaction Code</td><td class="py-2 text-right font-mono text-xs">{{ $payment->transaction_code ?: '—' }}</td></tr>
        <tr class="border-b"><td class="py-2 text-gray-500">Date Issued</td><td class="py-2 text-right">{{ $payment->created_at->format('d M Y H:i') }}</td></tr>
        <tr><td class="py-2 text-gray-500">Recorded By</td><td class="py-2 text-right">{{ $payment->recorded_by ? 'Staff #'.$payment->recorded_by : 'System (M-Pesa)' }}</td></tr>
      </table>

      <div class="mt-6 bg-green-50 border-l-4 border-green-600 p-3 text-xs text-green-800 rounded">
        This receipt is registered in Marell Academy's official database. If you suspect fraud, call <a href="tel:+254700000000" class="font-semibold underline">+254 700 000 000</a>.
      </div>
    </div>
  @else
    <div class="card p-8 border-t-4 border-red-600 text-center">
      <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
        <svg class="w-9 h-9 text-red-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
      </div>
      <div class="text-2xl font-extrabold text-red-700">NOT VERIFIED</div>
      <p class="text-sm text-gray-600 mt-3">No receipt with number <strong class="font-mono">{{ $receiptNo }}</strong> exists in our records.</p>
      <p class="text-xs text-gray-500 mt-4">This may be a fake receipt. Please contact the school immediately.</p>
      <a href="tel:+254700000000" class="btn btn-navy mt-6">Report Fraud</a>
    </div>
  @endif
</section>
@endsection
