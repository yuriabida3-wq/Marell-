<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { margin: 0; padding: 30px; font-size: 11px; }
  .header { border-bottom: 3px solid #0B3D91; padding-bottom: 15px; margin-bottom: 20px; }
  .logo { width: 50px; height: 50px; background: #D4AF37; color: #0B3D91; border-radius: 50%; text-align: center; line-height: 50px; font-weight: bold; font-size: 24px; display: inline-block; vertical-align: middle; }
  .school { display: inline-block; margin-left: 12px; vertical-align: middle; }
  .school-name { font-size: 22px; font-weight: bold; color: #0B3D91; }
  .school-tag { font-size: 9px; letter-spacing: 3px; color: #D4AF37; font-weight: bold; }
  .title { text-align: right; font-size: 22px; font-weight: bold; color: #0B3D91; }
  .info-table, .summary-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  .info-table td { padding: 6px 4px; }
  .info-table td.label { color: #6b7280; width: 30%; }
  .info-table td.value { font-weight: bold; color: #1f2937; }
  .summary-table th { background: #0B3D91; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
  .summary-table td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  .summary-table td.right { text-align: right; }
  .totals { margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px; }
  .totals td { padding: 4px 6px; font-size: 11px; }
  .totals td.label { color: #6b7280; }
  .totals td.value { font-weight: bold; color: #0B3D91; text-align: right; }
  .big { font-size: 16px; color: #dc2626 !important; }
  .footer { margin-top: 40px; padding-top: 15px; border-top: 2px solid #D4AF37; text-align: center; color: #6b7280; font-size: 9px; }
  .sig { margin-top: 30px; border-top: 1px solid #1f2937; width: 200px; padding-top: 4px; font-size: 9px; text-align: center; }
</style>
</head>
<body>

<div class="header">
  <div style="float:left;">
    <span class="logo">M</span>
    <span class="school"><div class="school-name">MARELL ACADEMY</div><div class="school-tag">EMPOWERING TOMORROW'S LEADERS</div></span>
  </div>
  <div style="float:right;">
    <div class="title">FEE STATEMENT</div>
    <div style="color:#6b7280;font-size:10px;">Issued: {{ now()->format('d M Y') }}</div>
  </div>
  <div style="clear:both;"></div>
</div>

<table class="info-table">
  <tr><td class="label">Student Name</td><td class="value">{{ $student->name }}</td></tr>
  <tr><td class="label">Admission Number</td><td class="value">{{ $student->adm_no }}</td></tr>
  <tr><td class="label">Class</td><td class="value">{{ $student->class }} {{ $student->stream }}</td></tr>
  <tr><td class="label">Parent / Guardian</td><td class="value">{{ $student->parent_name }}</td></tr>
</table>

<table class="summary-table" style="margin-top:20px;">
  <thead>
    <tr><th>DATE</th><th>RECEIPT</th><th>METHOD</th><th>REFERENCE</th><th style="text-align:right;">AMOUNT (KES)</th></tr>
  </thead>
  <tbody>
    @forelse ($payments as $p)
      <tr>
        <td>{{ $p->created_at->format('d M Y') }}</td>
        <td>{{ $p->receipt_no }}</td>
        <td>{{ $p->method }}</td>
        <td>{{ $p->transaction_code ?: '—' }}</td>
        <td class="right">{{ number_format($p->amount, 2) }}</td>
      </tr>
    @empty
      <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:20px;">No payments recorded yet</td></tr>
    @endforelse
  </tbody>
</table>

<table class="totals">
  <tr><td class="label">Total Fee</td><td class="value">KES {{ number_format($student->total_fee, 2) }}</td></tr>
  <tr><td class="label">Total Paid</td><td class="value" style="color:#16a34a;">KES {{ number_format($student->paid_amount, 2) }}</td></tr>
  <tr><td class="label">Outstanding Balance</td><td class="value big">KES {{ number_format($student->balance, 2) }}</td></tr>
</table>

<div style="margin-top:30px;">
  <div class="sig" style="float:right;">Bursar · Marell Academy</div>
  <div style="clear:both;"></div>
</div>

<div class="footer">
  Marell Academy · Kanduyi Road, Bungoma, Kenya · +254 700 000 000 · info@marell.ac.ke<br>
  This statement is computer-generated and valid without a physical signature.
</div>

</body>
</html>
