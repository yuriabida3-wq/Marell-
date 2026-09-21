@extends('layouts.public')
@section('title', 'WhatsApp Bot Demo')
@section('content')
<section class="bg-navy text-white py-12">
  <div class="max-w-3xl mx-auto px-4 text-center">
    <div class="text-gold font-bold tracking-widest text-sm">WHATSAPP BOT</div>
    <h1 class="text-3xl font-extrabold mt-2">Try the Bot</h1>
    <p class="text-white/80 mt-2 text-sm">Send messages like a real parent. Live bot replies.</p>
  </div>
</section>

<section class="max-w-3xl mx-auto px-4 py-10">
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="bg-[#25D366] text-white p-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-white text-[#25D366] flex items-center justify-center font-bold">M</div>
      <div>
        <div class="font-bold">Marell Academy</div>
        <div class="text-xs opacity-90">online</div>
      </div>
    </div>

    <div id="chat" class="p-4 space-y-3 min-h-[400px] max-h-[500px] overflow-y-auto bg-[#ECE5DD]">
      <div class="flex justify-start">
        <div class="bg-white rounded-2xl rounded-bl-sm px-4 py-2 max-w-[80%] shadow text-sm">
          Karibu Marell Bot!<br>
          Try typing:<br>
          <code class="text-xs bg-gray-100 px-1 rounded">balance MRL001</code><br>
          <code class="text-xs bg-gray-100 px-1 rounded">help</code><br>
          <code class="text-xs bg-gray-100 px-1 rounded">receipt</code>
        </div>
      </div>
    </div>

    <div class="p-4 bg-white border-t">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
        <input id="phone" placeholder="Parent phone" value="254720001111" class="min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm focus:border-gold focus:outline-none">
        <input id="message" placeholder="Type a message..." onkeypress="if(event.key==='Enter') send()" class="md:col-span-2 min-h-[42px] rounded-xl border-2 border-gray-200 px-3 text-sm focus:border-gold focus:outline-none">
      </div>
      <div class="flex flex-wrap gap-2 mb-3">
        <button onclick="quick('help')" class="text-xs px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200">help</button>
        <button onclick="quick('balance')" class="text-xs px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200">balance</button>
        <button onclick="quick('receipt')" class="text-xs px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200">receipt</button>
        <button onclick="quick('results')" class="text-xs px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200">results</button>
        <button onclick="quick('pay')" class="text-xs px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200">pay</button>
      </div>
      <button onclick="send()" class="w-full min-h-[48px] rounded-xl bg-[#25D366] text-white font-bold hover:scale-[1.02] transition">Send Message</button>
    </div>
  </div>

  <div class="mt-6 bg-gold/10 border-l-4 border-gold rounded-xl p-4 text-sm text-navy">
    <strong>Demo Mode:</strong> This is fully functional. To go live, connect a WhatsApp Business account (Meta Cloud API, Twilio, or Africa's Talking WhatsApp). The webhook is already at <code class="text-xs">/webhook/whatsapp</code>.
  </div>
</section>

@push('scripts')
<script>
async function send() {
  const phone = document.getElementById('phone').value.trim();
  const msg = document.getElementById('message').value.trim();
  if (!phone || !msg) return;

  addBubble(msg, 'user');
  document.getElementById('message').value = '';

  try {
    const r = await fetch('/whatsapp-demo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
      body: JSON.stringify({ phone, message: msg })
    });
    const d = await r.json();
    addBubble(d.reply, 'bot');
  } catch (e) {
    addBubble('Bot error: ' + e.message, 'bot');
  }
}

function quick(text) {
  document.getElementById('message').value = text;
  send();
}

function addBubble(text, who) {
  const chat = document.getElementById('chat');
  const wrap = document.createElement('div');
  wrap.className = who === 'user' ? 'flex justify-end' : 'flex justify-start';
  const bubble = document.createElement('div');
  bubble.className = (who === 'user'
    ? 'bg-[#DCF8C6] rounded-2xl rounded-br-sm'
    : 'bg-white rounded-2xl rounded-bl-sm') + ' px-4 py-2 max-w-[80%] shadow text-sm whitespace-pre-wrap';
  bubble.textContent = text;
  wrap.appendChild(bubble);
  chat.appendChild(wrap);
  chat.scrollTop = chat.scrollHeight;
}
</script>
@endpush
@endsection
