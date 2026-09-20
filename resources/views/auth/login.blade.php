<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login — Marell Academy</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: { navy:'#0B3D91', gold:'#D4AF37' }, fontFamily: { poppins:['Poppins','sans-serif'], inter:['Inter','sans-serif'] } } } }
</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  h1,h2 { font-family: 'Poppins', sans-serif; }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-navy via-[#082f6f] to-[#051d44] flex items-center justify-center p-4">

<div class="w-full max-w-md">
  <div class="text-center mb-8">
    <div class="w-16 h-16 mx-auto rounded-full bg-gold flex items-center justify-center font-bold text-navy text-2xl shadow-2xl">M</div>
    <div class="mt-3 text-white font-extrabold text-2xl" style="font-family:Poppins">MARELL ACADEMY</div>
    <div class="text-gold text-[10px] tracking-[0.3em] font-bold">STAFF PORTAL</div>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl p-8">
    <h1 class="text-2xl font-extrabold text-navy mb-1">Sign In</h1>
    <p class="text-sm text-gray-500 mb-6">Principal · DOS · Bursar · Teacher</p>

    @if ($errors->any())
      <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-lg mb-4">
        <div class="text-sm text-red-700">{{ $errors->first() }}</div>
      </div>
    @endif

    <form method="POST" action="/login" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">EMAIL</label>
        <input name="email" type="email" value="{{ old('email') }}" required autofocus
               class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PASSWORD</label>
        <input name="password" type="password" required
               class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none">
      </div>

      <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="remember" class="w-4 h-4">
        Remember me
      </label>

      <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-105 transition shadow-lg">
        Sign In →
      </button>
    </form>
  </div>

  <div class="text-center mt-6 text-xs text-white/60">
    <a href="/" class="hover:text-gold">← Back to website</a>
  </div>
</div>

</body>
</html>
