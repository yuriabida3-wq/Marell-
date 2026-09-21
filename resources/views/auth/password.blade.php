<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password — Marell</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif}h1{font-family:Poppins,sans-serif;font-weight:700}</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
  <div class="text-center mb-6">
    <div class="w-12 h-12 mx-auto rounded-full bg-navy text-white flex items-center justify-center font-bold">M</div>
    <h1 class="text-2xl text-navy mt-3">Change Password</h1>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-2xl">
    @if (session('success'))<div class="bg-green-50 border-l-4 border-green-600 p-3 rounded mb-4 text-sm text-green-700">✅ {{ session('success') }}</div>@endif
    @if ($errors->any())<div class="bg-red-50 border-l-4 border-red-600 p-3 rounded mb-4 text-sm text-red-700">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
      @csrf
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CURRENT PASSWORD *</label><input name="current_password" type="password" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">NEW PASSWORD *</label><input name="password" type="password" required minlength="6" class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
      <div><label class="block text-xs font-semibold text-navy mb-2 tracking-widest">CONFIRM *</label><input name="password_confirmation" type="password" required class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none"></div>
      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold">Update Password</button>
    </form>
    <div class="text-center mt-4 text-xs"><a href="/" class="text-navy hover:text-gold">← Back to home</a> | <a href="/parent/login" class="text-navy hover:text-gold">Parent Login</a></div>
  </div>
</div>
</body>
</html>
