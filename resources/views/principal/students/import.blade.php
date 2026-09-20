@extends('layouts.admin')
@section('title', 'Import Students')
@section('content')

<div class="mb-6">
  <a href="{{ route('principal.students.index') }}" class="text-xs text-gray-500 hover:text-navy">← All Students</a>
  <h1 class="text-2xl md:text-3xl font-extrabold text-navy mt-1">Import Students (CSV)</h1>
</div>

@if (session('error'))
  <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg mb-4"><div class="text-sm text-red-700">{{ session('error') }}</div></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

  {{-- UPLOAD --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📥 Upload CSV</h2>

    <form method="POST" action="{{ route('principal.students.import.store') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CSV FILE *</label>
        <input type="file" name="csv" accept=".csv,.txt" required class="w-full text-sm">
      </div>

      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition">📤 Upload & Import</button>
    </form>

    <div class="mt-6 text-xs text-gray-500">
      <strong>Required headers:</strong> <code>name, class, parent_name, parent_phone, total_fee</code><br>
      <strong>Optional:</strong> <code>stream, parent_email</code><br>
      ADM numbers are auto-generated.
    </div>
  </div>

  {{-- TEMPLATE --}}
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="font-extrabold text-navy mb-4">📋 Sample Template</h2>
    <pre class="text-xs bg-gray-50 rounded-xl p-4 overflow-x-auto">name,class,stream,parent_name,parent_phone,total_fee,parent_email
John Kamau,Grade 5,Blue,Peter Kamau,0712345678,45000,peter@example.com
Mary Achieng,Grade 4,Green,Alice Achieng,0723456789,40000,</pre>
    <a href="data:text/csv;charset=utf-8,name%2Cclass%2Cstream%2Cparent_name%2Cparent_phone%2Ctotal_fee%2Cparent_email%0AJohn%20Kamau%2CGrade%205%2CBlue%2CPeter%20Kamau%2C0712345678%2C45000%2Cpeter%40example.com%0AMary%20Achieng%2CGrade%204%2CGreen%2CAlice%20Achieng%2C0723456789%2C40000%2C"
       download="students-template.csv"
       class="inline-block mt-4 px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:scale-105 transition">
      ⬇️ Download Template
    </a>
  </div>

</div>

@endsection
