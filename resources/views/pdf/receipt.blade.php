<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { margin: 0; padding: 30px; color: #1f2937; font-size: 12px; }
  .header { border-bottom: 3px solid #0B3D91; padding-bottom: 15px; margin-bottom: 20px; }
  .logo { width: 50px; height: 50px; background: #D4AF37; color: #0B3D91; border-radius: 50%; text-align: center; line-height: 50px; font-weight: bold; font-size: 24px; display: inline-block; vertical-align: middle; }
  .school { display: inline-block; margin-left: 12px; vertical-align: middle; }
  .school-name { font-size: 22px; font-weight: bold; color: #0B3D91; }
  .school-tag { font-size: 9px; letter-spacing: 3px; color: #D4AF37; font-weight: bold; }
  .receipt-title { text-align: right; font-size: 26px; font-weight: bold; color: #0B3D91; }
  .receipt-no { text-align: right; color: #6b7280; font-size: 11px; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; }
  .label { color: #6b7280; width: 40%; }
  .value { font-weight: bold; color: #1f2937; }
  .amount-box { background: #f0fdf4; border: 2px solid #16a34a; border-radius: 8px; padding: 15px; text-align: center; margin: 20px 0; }
  .amount { font-size: 32px; font-weight: bold; color: #16a34a; }
  .footer { margin-top: 40px; padding-top: 15px; border-top: 2px solid #D4AF37; text-align: center; color: #6b7280; font-size: 10px; }
  .stamp { display: inline-block; margin-top: 20px; padding: 8px 20px; border: 3px double #0B3D91; color: #0B3D91; font-weight: bold; font-size: 12px; transform: rotate(-5deg); }
  .signature { margin-top: 30px; }
  .signature-line { border-top: 1px solid #1f2937; width: 200px; margin-top: 40px; padding-top: 4px; font-size: 10px; }
</style>
</head>
<body>

<div class="header">
  <div style="float:left;">
    <span class="logo">M</span>
    <span class="school">
      <div class="school-name">MARELL ACADEMY</div>
      <div class="school-tag">EMPOWERING TOMORROW'S LEADERS</div>
    </span>
  </div>
  <div style="float:right;">
    <div class="receipt-title">OFFICIAL RECEIPT</div>
    <div class="receipt-no">No: {{ $payment->receipt_no }}</div>
    <div class="receipt-no">Date: {{ $payment->created_at->format('d M Y H:i') }}</div>
  </div>
  <div style="clear:both;"></div>
</div>

<div class="amount-box">
  <div style="color:#6b7280;font-size:11px;">AMOUNT RECEIVED</div>
  <div class="amount">KES {{ number_format($payment->amount, 2) }}</div>
</div>

<table>
  <tr><td class="label">Student Name</td><td class="value">{{ $student->name }}</td></tr>
  <tr><td class="label">Admission Number</td><td class="value">{{ $student->adm_no }}</td></tr>
  <tr><td class="label">Class</td><td class="value">{{ $student->class }} {{ $student->stream }}</td></tr>
  <tr><td class="label">Parent / Guardian</td><td class="value">{{ $student->parent_name }}</td></tr>
  <tr><td class="label">Payment Method</td><td class="value">{{ $payment->method }}</td></tr>
  <tr><td class="label">Transaction Code</td><td class="value">{{ $payment->transaction_code ?: '—' }}</td></tr>
  <tr><td class="label">Term / Year</td><td class="value">{{ $payment->term }} / {{ $payment->year }}</td></tr>
  <tr><td class="label">Total Fee</td><td class="value">KES {{ number_format($student->total_fee, 2) }}</td></tr>
  <tr><td class="label">Total Paid to Date</td><td class="value">KES {{ number_format($student->paid_amount, 2) }}</td></tr>
  <tr><td class="label">Outstanding Balance</td><td class="value" style="color:#dc2626;">KES {{ number_format($student->balance, 2) }}</td></tr>
</table>

<div style="margin-top:30px;">
  <div style="float:left; width:50%;">
    <div class="stamp">PAID</div>
  </div>
  <div style="float:right; width:50%; text-align:right;">
    <div class="signature">
      <div class="signature-line" style="display:inline-block; text-align:center;">
        Authorized Signature<br>
        Bursar · Marell Academy
      </div>
    </div>
  </div>
  <div style="clear:both;"></div>
</div>

<div class="footer">
  Marell Academy · Kanduyi Road, Bungoma, Kenya · +254 700 000 000 · info@marell.ac.ke<br>
  This receipt is computer-generated and valid without a physical signature.<br>
  Verify at marell.ac.ke/verify/{{ $payment->receipt_no }}
</div>

</body>
</html>
