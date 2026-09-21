<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { margin: 0; padding: 30px; font-size: 11px; color: #1f2937; }
  .header { border-bottom: 3px solid #0B3D91; padding-bottom: 15px; margin-bottom: 20px; }
  .logo { width: 50px; height: 50px; background: #D4AF37; color: #0B3D91; border-radius: 50%; text-align: center; line-height: 50px; font-weight: bold; font-size: 24px; display: inline-block; vertical-align: middle; }
  .school { display: inline-block; margin-left: 12px; vertical-align: middle; }
  .school-name { font-size: 22px; font-weight: bold; color: #0B3D91; }
  .school-tag { font-size: 9px; letter-spacing: 3px; color: #D4AF37; font-weight: bold; }
  .title { text-align: right; font-size: 20px; font-weight: bold; color: #0B3D91; }
  .period { text-align: right; font-size: 10px; color: #6b7280; }
  h2 { color: #0B3D91; font-size: 13px; margin: 20px 0 8px; border-bottom: 1px solid #D4AF37; padding-bottom: 4px; }
  .kpi-grid { display: table; width: 100%; table-layout: fixed; margin: 10px 0; }
  .kpi { display: table-cell; padding: 10px; text-align: center; border-right: 1px solid #e5e7eb; }
  .kpi:last-child { border-right: 0; }
  .kpi-label { font-size: 8px; color: #6b7280; letter-spacing: 1.5px; text-transform: uppercase; }
  .kpi-value { font-size: 16px; font-weight: bold; color: #0B3D91; margin-top: 4px; }
  .kpi-value.green { color: #16a34a; }
  .kpi-value.red { color: #dc2626; }
  table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  th { background: #0B3D91; color: white; padding: 6px 8px; text-align: left; font-size: 9px; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  td.right { text-align: right; }
  .net-box { background: #fef3c7; border: 2px solid #D4AF37; padding: 12px; text-align: center; margin: 15px 0; }
  .net-amount { font-size: 20px; font-weight: bold; color: #0B3D91; }
  .signatures { margin-top: 40px; display: table; width: 100%; }
  .sig-cell { display: table-cell; width: 50%; text-align: center; padding: 30px 20px 0; }
  .sig-line { border-top: 1px solid #1f2937; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #374151; }
  .footer { margin-top: 40px; padding-top: 12px; border-top: 2px solid #D4AF37; text-align: center; color: #6b7280; font-size: 9px; }
  .bar { height: 12px; background: #D4AF37; display: inline-block; vertical-align: middle; }
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
    <div class="title">BOARD REPORT</div>
    <div class="period">{{ date('d M Y', strtotime($from)) }} to {{ date('d M Y', strtotime($to)) }}</div>
    <div class="period">Generated: {{ now()->format('d M Y H:i') }}</div>
  </div>
  <div style="clear:both;"></div>
</div>

<h2>FINANCIAL SUMMARY</h2>
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-label">Total Income</div>
    <div class="kpi-value green">KES {{ number_format($income, 0) }}</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Total Expenses</div>
    <div class="kpi-value red">KES {{ number_format($expenses, 0) }}</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Net Position</div>
    <div class="kpi-value">KES {{ number_format($net, 0) }}</div>
  </div>
</div>

<div class="net-box">
  <div class="kpi-label">NET POSITION FOR PERIOD</div>
  <div class="net-amount">KES {{ number_format($net, 0) }}</div>
</div>

<h2>INCOME BY PAYMENT METHOD</h2>
<table>
  <thead><tr><th>Method</th><th class="right">Transactions</th><th class="right">Amount (KES)</th><th class="right">Share</th></tr></thead>
  <tbody>
    @foreach ($byMethod as $m)
      <tr>
        <td>{{ $m->method }}</td>
        <td class="right">{{ number_format($m->cnt) }}</td>
        <td class="right">{{ number_format($m->total, 2) }}</td>
        <td class="right">{{ $income > 0 ? round(($m->total / $income) * 100, 1) : 0 }}%</td>
      </tr>
    @endforeach
  </tbody>
</table>

<h2>INCOME BY CLASS</h2>
<table>
  <thead><tr><th>Class</th><th class="right">Collected (KES)</th><th class="right">Share</th></tr></thead>
  <tbody>
    @forelse ($byClass as $c)
      <tr>
        <td>{{ $c->class }}</td>
        <td class="right">{{ number_format($c->total, 2) }}</td>
        <td class="right">{{ $income > 0 ? round(($c->total / $income) * 100, 1) : 0 }}%</td>
      </tr>
    @empty
      <tr><td colspan="3" style="text-align:center;color:#9ca3af;">No collections in period</td></tr>
    @endforelse
  </tbody>
</table>

<h2>EXPENSES BY CATEGORY</h2>
<table>
  <thead><tr><th>Category</th><th class="right">Amount (KES)</th><th class="right">Share</th></tr></thead>
  <tbody>
    @forelse ($expenseByCategory as $e)
      <tr>
        <td>{{ $e->category }}</td>
        <td class="right">{{ number_format($e->total, 2) }}</td>
        <td class="right">{{ $expenses > 0 ? round(($e->total / $expenses) * 100, 1) : 0 }}%</td>
      </tr>
    @empty
      <tr><td colspan="3" style="text-align:center;color:#9ca3af;">No expenses in period</td></tr>
    @endforelse
  </tbody>
</table>

<h2>SCHOOL POSITION</h2>
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-label">Active Students</div>
    <div class="kpi-value">{{ number_format($totalStudents) }}</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Fee Defaulters</div>
    <div class="kpi-value red">{{ number_format($defaulters) }}</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Outstanding Fees</div>
    <div class="kpi-value red">KES {{ number_format($outstanding, 0) }}</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Collection Rate</div>
    <div class="kpi-value green">
      @php
        $totalFee  = \App\Models\Student::where('status','active')->sum('total_fee');
        $collected = \App\Models\Student::where('status','active')->sum('paid_amount');
        echo $totalFee > 0 ? round(($collected / $totalFee) * 100, 1) : 0;
      @endphp%
    </div>
  </div>
</div>

<h2>DAILY COLLECTION TREND</h2>
@if ($trend->count())
  @php $maxTrend = $trend->max('total'); @endphp
  <table>
    <thead><tr><th style="width:100px;">Date</th><th>Trend</th><th class="right" style="width:100px;">Amount (KES)</th></tr></thead>
    <tbody>
      @foreach ($trend as $t)
        <tr>
          <td>{{ date('d M', strtotime($t->day)) }}</td>
          <td>
            @php $w = $maxTrend > 0 ? round(($t->total / $maxTrend) * 100) : 0; @endphp
            <div class="bar" style="width: {{ $w }}%;"></div>
          </td>
          <td class="right">{{ number_format($t->total, 0) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@else
  <p style="text-align:center;color:#9ca3af;padding:15px;">No transactions in period.</p>
@endif

<div class="signatures">
  <div class="sig-cell"><div class="sig-line">Director / Principal<br>Signature &amp; Date</div></div>
  <div class="sig-cell"><div class="sig-line">Bursar<br>Signature &amp; Date</div></div>
</div>

<div class="footer">
  Marell Academy · Kanduyi Road, Bungoma, Kenya · +254 700 000 000 · info@marell.ac.ke<br>
  This report is auto-generated by Jenga Web School OS. Confidential — for Board use only.
</div>

</body>
</html>
