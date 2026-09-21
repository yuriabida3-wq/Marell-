<style>
#vaBtn{position:fixed;bottom:6rem;right:1.5rem;z-index:40;width:56px;height:56px;border-radius:50%;background:#0B3D91;color:#fff;box-shadow:0 10px 30px rgba(11,61,145,.4);display:flex;align-items:center;justify-content:center;border:none;cursor:pointer}
#vaBtn:hover{transform:scale(1.1);transition:transform .15s}
#vaPnl{position:fixed;bottom:6rem;right:1.5rem;z-index:50;width:340px;max-width:calc(100vw - 3rem);height:520px;max-height:calc(100vh - 8rem);background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);display:none;flex-direction:column;overflow:hidden;border:1px solid #e5e7eb}
#vaPnl.flex{display:flex}
.va-hdr{background:#0B3D91;color:#fff;padding:1rem;display:flex;align-items:center;gap:.75rem}
.va-av{width:40px;height:40px;border-radius:50%;background:#D4AF37;color:#0B3D91;display:flex;align-items:center;justify-content:center;font-weight:bold}
.va-ttl{font-weight:bold;font-size:14px}
.va-st{font-size:10px;color:#86efac}
.va-x{background:none;border:none;color:rgba(255,255,255,.7);font-size:24px;line-height:1;cursor:pointer}
#vaBody{flex:1;overflow-y:auto;padding:1rem;background:#f9fafb}
.va-msg{margin-bottom:.5rem;display:flex}
.va-msg.u{justify-content:flex-end}
.va-bub{padding:.5rem .75rem;max-width:85%;box-shadow:0 1px 3px rgba(0,0,0,.08);font-size:13px;white-space:pre-wrap;line-height:1.4}
.va-b{background:#fff;color:#1f2937;border:1px solid #e5e7eb;border-radius:1rem 1rem 1rem .25rem}
.va-u{background:#0B3D91;color:#fff;border-radius:1rem 1rem .25rem 1rem}
.va-act{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem}
.va-act a{display:inline-flex;align-items:center;padding:.4rem .75rem;border-radius:99px;background:#D4AF37;color:#0B3D91;text-decoration:none;font-size:12px;font-weight:600}
.va-ft{padding:.75rem;background:#fff;border-top:1px solid #e5e7eb;display:flex;gap:.5rem}
.va-ft input{flex:1;min-height:40px;border-radius:.75rem;border:2px solid #e5e7eb;padding:0 .75rem;font-size:14px}
.va-ft button{width:40px;height:40px;border-radius:.75rem;background:#D4AF37;color:#0B3D91;border:none;cursor:pointer;font-weight:bold}
</style>

<button id="vaBtn" aria-label="Open assistant">
  <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
</button>

<div id="vaPnl">
  <div class="va-hdr">
    <div class="va-av">M</div>
    <div style="flex:1"><div class="va-ttl">Marell Helper</div><div class="va-st">● Online</div></div>
    <button id="vaX" class="va-x">&times;</button>
  </div>
  <div id="vaBody"></div>
  <div class="va-ft">
    <input id="vaIn" type="text" placeholder="Ask anything..." maxlength="500">
    <button id="vaSend">→</button>
  </div>
</div>

<script>
(function(){
  var b=document.getElementById('vaBtn'),p=document.getElementById('vaPnl'),x=document.getElementById('vaX'),
      bd=document.getElementById('vaBody'),inp=document.getElementById('vaIn'),sd=document.getElementById('vaSend');
  var greeted=false;

  function bubble(t,w){
    var r=document.createElement('div'); r.className='va-msg'+(w==='u'?' u':'');
    var b2=document.createElement('div'); b2.className='va-bub '+(w==='u'?'va-u':'va-b'); b2.textContent=t;
    r.appendChild(b2); bd.appendChild(r); bd.scrollTop=bd.scrollHeight;
  }
  function actions(a){
    if(!a||!a.length)return;
    var w=document.createElement('div'); w.className='va-act';
    a.forEach(function(x){ var l=document.createElement('a'); l.href=x.url; l.textContent=x.label; w.appendChild(l); });
    bd.appendChild(w); bd.scrollTop=bd.scrollHeight;
  }
  function open(){
    p.classList.add('flex');
    if(!greeted){greeted=true;bubble("Hi! I'm Marell Helper. Ask me about fees, results, timetable, admissions, or anything else.",'b');
      actions([{label:'💰 Pay Fees',url:'/pay'},{label:'📊 Results',url:'/results'},{label:'📅 Timetable',url:'/timetable'},{label:'📝 Admissions',url:'/admissions'}]);}
    setTimeout(function(){inp.focus()},200);
  }
  function close(){p.classList.remove('flex');}
  async function send(){
    var m=inp.value.trim(); if(!m)return;
    bubble(m,'u'); inp.value='';
    var t=document.createElement('div'); t.style.cssText='font-size:12px;color:#9ca3af;padding:.25rem 0'; t.textContent='typing...';
    bd.appendChild(t); bd.scrollTop=bd.scrollHeight;
    try{
      var r=await fetch('/assistant/ask',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json'},body:JSON.stringify({message:m})});
      var d=await r.json(); t.remove();
      bubble(d.reply||'Sorry, I did not catch that.','b');
      if(d.actions)actions(d.actions);
    }catch(e){t.remove();bubble('Something went wrong. Call +254 700 000 000.','b');}
  }
  b.addEventListener('click',function(){ p.classList.contains('flex')?close():open(); });
  x.addEventListener('click',close);
  sd.addEventListener('click',send);
  inp.addEventListener('keypress',function(e){if(e.key==='Enter')send()});
})();
</script>
