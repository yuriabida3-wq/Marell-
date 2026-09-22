<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Security Login — Marell Academy</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'},fontFamily:{poppins:['Poppins','sans-serif'],inter:['Inter','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif}h1{font-family:Poppins,sans-serif;font-weight:800}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-navy to-[#051937] flex items-center justify-center p-4">

<div class="w-full max-w-md">
  <div class="text-center mb-8">
    <div class="w-20 h-20 mx-auto rounded-full bg-gold flex items-center justify-center font-bold text-navy text-3xl shadow-2xl">M</div>
    <div class="mt-4 text-white font-extrabold text-2xl">MARELL ACADEMY</div>
    <div class="text-gold text-xs tracking-widest font-bold mt-1">SECURITY GATE</div>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl p-8">
    <h1 class="text-2xl text-navy mb-1">Gate Login</h1>
    <p class="text-sm text-gray-500 mb-6">Enter your phone and PIN to continue</p>

    @if ($errors->any())
      <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded mb-4">
        <div class="text-sm text-red-700">{{ $errors->first() }}</div>
      </div>
    @endif

    <form method="POST" action="{{ route('security.login.post') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE NUMBER</label>
        <input name="phone" value="{{ old('phone') }}" required autofocus placeholder="07XXXXXXXX"
               class="w-full min-h-[56px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">4-DIGIT PIN</label>
        <input name="pin" type="password" required maxlength="8" inputmode="numeric"
               class="w-full min-h-[56px] rounded-xl border-2 border-gray-200 px-4 text-2xl text-center tracking-[0.5em] focus:border-gold focus:outline-none">
      </div>

      <button class="w-full min-h-[56px] rounded-xl bg-gold text-navy font-bold text-lg hover:scale-[1.02] transition">
        🔓 Login
      </button>
    </form>

    <div class="mt-6 text-center text-xs text-gray-500">
      Forgot PIN? Contact the Director.
    </div>
  </div>

  <div class="text-center mt-6 text-xs text-white/60">
    <a href="/" class="hover:text-gold">← Back to website</a>
  </div>
</div>

</body>
</html>
