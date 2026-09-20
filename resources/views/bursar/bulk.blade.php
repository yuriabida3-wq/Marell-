@extends('layouts.bursar')
@section('title', 'Bulk Cash Upload')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy">Bulk Cash Payment Upload</h1>
  <p class="text-sm text-gray-500 mt-1">Upload a CSV of multiple payments at once.</p>
</div>

@if (session('success'))
  <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-lg mb-4"><div class="font-bold text-green-700">✅ {{ session('success') }}</div></div>
@endif
@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="font-bold text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📥 Upload CSV</h2>
    <form method="POST" action="{{ route('bursar.bulk.upload') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <input type="file" name="csv" accept=".csv,.txt" required class="w-full text-sm">
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">📤 Upload & Process</button>
    </form>
    <div class="mt-4 text-xs text-gray-500">
      <strong>Required headers:</strong> <code>adm_no, amount, method</code><br>
      <strong>method</strong> = Cash / Bank / M-Pesa
    </div>
  </div>

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📋 Sample</h2>
    <pre class="text-xs bg-gray-50 rounded-xl p-4">adm_no,amount,method
MAR-2024-0001,5000,Cash
MAR-2024-0002,3500,Bank
MAR-2024-0003,8000,Cash</pre>
    <a href="data:text/csv;charset=utf-8,adm_no%2Camount%2Cmethod%0AMAR-2024-0001%2C5000%2CCash%0AMAR-2024-0002%2C3500%2CBank"
       download="bulk-payments-template.csv"
       class="inline-block mt-3 px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold">⬇️ Download Template</a>
  </div>
</div>

@endsection
