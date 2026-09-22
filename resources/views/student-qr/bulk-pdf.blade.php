<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { margin: 0; padding: 10px; }
  .page { page-break-after: always; padding: 10px; }
  .card { border: 4px solid #0B3D91; border-radius: 12px; padding: 20px; text-align: center; width: 320px; margin: 0 auto; }
  .school { font-size: 10px; letter-spacing: 3px; color: #D4AF37; font-weight: bold; }
  .name { font-size: 22px; font-weight: bold; color: #0B3D91; margin: 8px 0; }
  .meta { font-size: 11px; color: #6b7280; margin-bottom: 15px; }
  .qr { margin: 15px 0; }
  .hint { font-size: 9px; color: #6b7280; margin-top: 12px; }
  .grid { display: table; width: 100%; }
</style>
</head>
<body>

@foreach ($students as $student)
  <div class="page">
    <div class="card">
      <div class="school">MARELL ACADEMY</div>
      <div class="name">{{ $student->name }}</div>
      <div class="meta">{{ $student->adm_no }} · {{ $student->class }} {{ $student->stream }}</div>
      <div class="qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($student->qrUrl()) }}" style="width:240px;height:240px;">
      </div>
      <div class="hint">Scan for canteen · library · bus · gate</div>
    </div>
  </div>
@endforeach

</body>
</html>
