<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Marell Academy</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#0B3D91',gold:'#D4AF37'},fontFamily:{poppins:['Poppins','sans-serif'],inter:['Inter','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  h1, h2 { font-family: 'Poppins', sans-serif; font-weight: 800; }
  .tab-btn { padding: 0.75rem 1rem; font-weight: 600; font-size: 0.875rem; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.15s; color: #6b7280; }
  .tab-btn.active { color: #0B3D91; border-bottom-color: #D4AF37; }
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-navy via-[#082f6f] to-[#051d44] flex items-center justify-center p-4">

<div class="w-full max-w-md">
  {{-- LOGO --}}
  <div class="text-center mb-6">
    <div class="w-16 h-16 mx-auto rounded-full bg-gold flex items-center justify-center font-bold text-navy text-2xl shadow-2xl">M</div>
    <div class="mt-3 text-white font-extrabold text-2xl" style="font-family:Poppins">MARELL ACADEMY</div>
    <div class="text-gold text-[10px] tracking-[0.3em] font-bold mt-1">SCHOOL PORTAL</div>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

    {{-- TABS --}}
    <div class="flex border-b">
      <button id="tab-staff" onclick="showTab('staff')" class="tab-btn flex-1 active">👨‍💼 Staff</button>
      <button id="tab-guard" onclick="showTab('guard')" class="tab-btn flex-1">🛡️ Guard</button>
      <button id="tab-parent" onclick="showTab('parent')" class="tab-btn flex-1">👨‍👩‍👧 Parent</button>
    </div>

    <div class="p-6 md:p-8">

      {{-- FLASH MESSAGES --}}
      @if (session('otp_sent'))
        <div class="bg-green-50 border-l-4 border-green-600 p-3 rounded-lg mb-4 text-sm text-green-700">
          ✅ Code sent via SMS. Enter the 6-digit code below.
        </div>
      @endif
      @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-600 p-3 rounded-lg mb-4 text-sm text-green-700">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-lg mb-4 text-sm text-red-700">{{ session('error') }}</div>
      @endif

      {{-- ==================== STAFF PANEL ==================== --}}
      <div id="panel-staff" class="tab-panel active">
        <h2 class="text-xl text-navy mb-1">Staff Login</h2>
        <p class="text-xs text-gray-500 mb-5">Principal · DOS · Bursar · Teacher · Security</p>

        @if ($errors->has('email') || $errors->has('password'))
          <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-lg mb-4 text-sm text-red-700">
            {{ $errors->first('email') ?: $errors->first('password') }}
          </div>
        @endif

        <form method="POST" action="{{ route('login.staff') }}" class="space-y-4">
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
          <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition shadow-lg">
            Sign In →
          </button>
        </form>
      </div>

      {{-- ==================== GUARD PANEL ==================== --}}
      <div id="panel-guard" class="tab-panel">
        <h2 class="text-xl text-navy mb-1">Security Guard Login</h2>
        <p class="text-xs text-gray-500 mb-5">Phone + 4-digit PIN</p>

        @if ($errors->has('guard_phone') || $errors->has('guard_pin'))
          <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-lg mb-4 text-sm text-red-700">
            {{ $errors->first('guard_phone') ?: $errors->first('guard_pin') }}
          </div>
        @endif

        <form method="POST" action="{{ route('login.guard') }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE NUMBER</label>
            <input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" required
                   class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">4-DIGIT PIN</label>
            <input name="pin" type="password" required inputmode="numeric" maxlength="8"
                   class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-2xl text-center tracking-[0.5em] focus:border-gold focus:outline-none">
          </div>
          <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition shadow-lg">
            🔓 Gate Login
          </button>
        </form>
      </div>

      {{-- ==================== PARENT PANEL ==================== --}}
      <div id="panel-parent" class="tab-panel">
        @if (session('parent_pending_phone'))
          {{-- STEP 2: VERIFY OTP --}}
          <h2 class="text-xl text-navy mb-1">Enter Code</h2>
          <p class="text-xs text-gray-500 mb-5">We sent a 6-digit code to {{ session('parent_pending_phone') }}</p>

          @if ($errors->has('otp'))
            <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-lg mb-4 text-sm text-red-700">
              {{ $errors->first('otp') }}
            </div>
          @endif

          <form method="POST" action="{{ route('login.parent.verify') }}" class="space-y-4">
            @csrf
            <div>
              <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">6-DIGIT CODE</label>
              <input name="otp" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autofocus placeholder="123456"
                     class="w-full min-h-[56px] rounded-xl border-2 border-gray-200 px-4 text-2xl text-center tracking-[0.5em] font-bold focus:border-gold focus:outline-none">
            </div>
            <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition shadow-lg">
              ✅ Verify & Log In
            </button>
          </form>

          <div class="text-center mt-4">
            <a href="{{ route('login.hub') }}" class="text-xs text-navy underline">← Use different number</a>
          </div>
        @else
          {{-- STEP 1: REQUEST OTP --}}
          <h2 class="text-xl text-navy mb-1">Parent Login</h2>
          <p class="text-xs text-gray-500 mb-5">No password needed — SMS code</p>

          @if ($errors->has('parent_phone'))
            <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-lg mb-4 text-sm text-red-700">
              {{ $errors->first('parent_phone') }}
            </div>
          @endif

          <form method="POST" action="{{ route('login.parent.send') }}" class="space-y-4">
            @csrf
            <div>
              <label class="block text-xs font-semibold text-navy mb-2 tracking-widest">PHONE NUMBER</label>
              <input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" required
                     class="w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 text-lg focus:border-gold focus:outline-none">
              <div class="text-xs text-gray-500 mt-1">Use the phone you gave the school during admission.</div>
            </div>
            <button class="w-full min-h-[48px] rounded-xl bg-gold text-navy font-bold hover:scale-[1.02] transition shadow-lg">
              📱 Send Login Code
            </button>
          </form>
        @endif
      </div>

    </div>
  </div>

  {{-- BACK TO SITE --}}
  <div class="text-center mt-6 text-xs text-white/60">
    <a href="/" class="hover:text-gold">← Back to website</a>
  </div>
</div>

<script>
function showTab(name) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

  document.getElementById('tab-' + name).classList.add('active');
  document.getElementById('panel-' + name).classList.add('active');
}

// Auto-open correct tab based on errors or session
@if (session('parent_pending_phone'))
  showTab('parent');
@elseif ($errors->has('guard_phone') || $errors->has('guard_pin'))
  showTab('guard');
@elseif ($errors->has('parent_phone'))
  showTab('parent');
@elseif ($errors->has('email') || $errors->has('password'))
  showTab('staff');
@elseif (session('otp_sent'))
  showTab('parent');
@endif
</script>

</body>
</html>
