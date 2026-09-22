<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Canteen Logs</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'}}}}</script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="bg-navy text-white p-4">
  <div class="max-w-4xl mx-auto flex items-center justify-between">
    <a href="{{ route('canteen.index') }}" class="text-sm">← Canteen</a>
    <div class="font-bold">📋 Canteen Logs (Today)</div>
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
          <th class="p-3">ITEMS</th>
          <th class="p-3 text-right">AMOUNT</th>
          <th class="p-3 text-right">BALANCE</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($logs as $l)
          <tr>
            <td class="p-3 text-xs text-gray-500">{{ $l->created_at->format('H:i') }}</td>
            <td class="p-3">
              <div class="font-semibold text-navy text-sm">{{ $l->student->name ?? '—' }}</div>
              <div class="text-xs text-gray-500">{{ $l->student->adm_no ?? '' }}</div>
            </td>
            <td class="p-3 text-xs text-gray-600">{{ $l->description ?? '—' }}</td>
            <td class="p-3 text-right font-bold text-red-600">- KES {{ number_format($l->amount, 0) }}</td>
            <td class="p-3 text-right text-xs">KES {{ number_format($l->balance_after, 0) }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-12 text-center text-gray-400">No purchases today</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $logs->links() }}</div>
</div>
</body>
</html>
