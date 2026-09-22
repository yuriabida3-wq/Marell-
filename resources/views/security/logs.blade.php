<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pickup Logs</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'}}}}</script>
<style>body{font-family:Inter,sans-serif}h1{font-family:Poppins,sans-serif;font-weight:800}</style>
</head>
<body class="min-h-screen bg-gray-100">

<div class="bg-navy text-white p-4">
  <div class="flex items-center justify-between max-w-4xl mx-auto">
    <a href="{{ route('security.dashboard') }}" class="text-sm">← Back</a>
    <div class="font-bold">📋 Pickup Logs</div>
    <div class="w-12"></div>
  </div>
</div>

<div class="max-w-4xl mx-auto p-4">

  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr class="text-left">
          <th class="p-3">TIME</th>
          <th class="p-3">STUDENT</th>
          <th class="p-3">PICKER</th>
          <th class="p-3">REL</th>
          <th class="p-3 text-center">RESULT</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($logs as $l)
          <tr>
            <td class="p-3 text-xs text-gray-500">{{ $l->created_at->format('d M H:i') }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy text-sm">{{ $l->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500 font-mono">{{ $l->student->adm_no ?? '' }}</div>
            </td>
            <td class="p-3">
              <div class="font-semibold text-sm">{{ $l->picker_name }}</div>
              <div class="text-xs text-gray-500">{{ $l->picker_phone }}</div>
            </td>
            <td class="p-3 text-xs">{{ $l->relationship ?? '—' }}</td>
            <td class="p-3 text-center">
              <span class="px-2 py-1 rounded-full text-xs font-bold {{ $l->resultColor() }}">
                {{ strtoupper($l->result) }}
              </span>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-12 text-center text-gray-400">No pickup logs yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $logs->links() }}</div>
</div>

</body>
</html>
