@extends('layouts.app')
@section('content')
<div class="p-4 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">🍔 Canteen Menu</h1>

    {{-- Student Lookup --}}
    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <label class="block text-sm font-bold mb-2">Scan Student QR or Enter ADM</label>
        <div class="flex gap-2">
            <input id="qr-input" type="text" placeholder="Scan QR or type ADM number..."
                   class="flex-1 border-2 border-gray-200 rounded-xl p-3" autofocus>
            <button onclick="lookupStudent()" class="bg-navy text-white px-6 py-3 rounded-xl font-bold">Look Up</button>
        </div>
        <div id="student-info" class="mt-3 hidden p-3 bg-gray-50 rounded-xl">
            <p class="font-bold" id="student-name"></p>
            <p class="text-sm text-gray-600" id="student-details"></p>
            <p class="text-lg font-bold text-green-700" id="student-balance"></p>
            <p class="text-xs text-red-600 hidden" id="student-blocked"></p>
        </div>
    </div>

    {{-- Menu Grid --}}
    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <h2 class="font-bold text-lg mb-3">Tap items to add to cart</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($items as $item)
                <button type="button"
                        onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, '{{ $item->category }}')"
                        class="border-2 border-gray-200 hover:border-navy rounded-xl p-3 text-left transition">
                    <p class="text-xs text-gray-500 uppercase">{{ $item->category }}</p>
                    <p class="font-bold">{{ $item->name }}</p>
                    <p class="text-lg font-bold text-navy">KES {{ number_format($item->price, 0) }}</p>
                    @if($item->stock > 0)
                        <p class="text-xs text-gray-500">Stock: {{ $item->stock }}</p>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Cart --}}
    <div class="bg-white rounded-xl shadow p-4">
        <h2 class="font-bold text-lg mb-3">🛒 Cart</h2>
        <div id="cart-items" class="mb-3 text-sm">
            <p class="text-gray-400">No items yet. Tap menu items above.</p>
        </div>
        <div class="flex justify-between items-center border-t pt-3">
            <p class="text-lg font-bold">Total: <span id="cart-total">KES 0</span></p>
            <div class="flex gap-2">
                <button onclick="clearCart()" class="px-4 py-2 rounded-xl border">Clear</button>
                <button onclick="checkout()" class="bg-gold text-navy px-6 py-2 rounded-xl font-bold">Charge Wallet</button>
            </div>
        </div>
        <p id="checkout-message" class="mt-3 text-sm"></p>
    </div>
</div>

<script>
    let currentStudent = null;
    let cart = [];

    function lookupStudent() {
        const val = document.getElementById('qr-input').value.trim();
        if (!val) return;

        // Accept either ADM number OR student_id (if QR contains it)
        fetch('{{ route('canteen.item-lookup') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({student_id: val})
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                currentStudent = data.student;
                document.getElementById('student-info').classList.remove('hidden');
                document.getElementById('student-name').textContent = data.student.name + ' (' + data.student.adm_no + ')';
                document.getElementById('student-details').textContent = data.student.class +
                    (data.student.daily_limit > 0 ? ' · Daily limit: KES ' + data.student.daily_limit : ' · No daily limit');
                document.getElementById('student-balance').textContent = 'Balance: KES ' + data.student.balance;

                const blocked = [...(data.student.blocked_items || []), ...(data.student.blocked_categories || []).map(c => 'ALL ' + c)];
                const blockedEl = document.getElementById('student-blocked');
                if (blocked.length > 0) {
                    blockedEl.classList.remove('hidden');
                    blockedEl.textContent = '🚫 Blocked: ' + blocked.join(', ');
                } else {
                    blockedEl.classList.add('hidden');
                }
            } else {
                alert('Student not found');
            }
        });
    }

    // Allow Enter key on QR input
    document.getElementById('qr-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); lookupStudent(); }
    });

    function addToCart(id, name, price, category) {
        const existing = cart.find(i => i.id === id);
        if (existing) { existing.qty++; }
        else { cart.push({id, name, price, category, qty: 1}); }
        renderCart();
    }

    function renderCart() {
        const el = document.getElementById('cart-items');
        if (cart.length === 0) {
            el.innerHTML = '<p class="text-gray-400">No items yet. Tap menu items above.</p>';
            document.getElementById('cart-total').textContent = 'KES 0';
            return;
        }
        let total = 0;
        el.innerHTML = cart.map((item, i) => {
            total += item.price * item.qty;
            return `<div class="flex justify-between items-center border-b py-1">
                <span>${item.qty}x ${item.name}</span>
                <span class="font-bold">KES ${(item.price * item.qty).toFixed(0)}</span>
                <button onclick="removeFromCart(${i})" class="text-red-500 text-xs">✕</button>
            </div>`;
        }).join('');
        document.getElementById('cart-total').textContent = 'KES ' + total.toFixed(0);
    }

    function removeFromCart(i) {
        cart.splice(i, 1);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
        document.getElementById('checkout-message').textContent = '';
    }

    function checkout() {
        if (!currentStudent) { alert('Look up a student first'); return; }
        if (cart.length === 0) { alert('Cart is empty'); return; }

        const msg = document.getElementById('checkout-message');
        msg.textContent = 'Processing...';
        msg.className = 'mt-3 text-sm text-gray-600';

        fetch('{{ route('canteen.item-checkout') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({
                student_id: currentStudent.id,
                item_ids: cart.map(i => i.id),
                quantities: cart.map(i => i.qty)
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                msg.className = 'mt-3 text-sm text-green-700 font-bold';
                msg.textContent = data.message + ' · Total: KES ' + data.total + ' · New balance: KES ' + data.new_balance + ' · Receipt: ' + data.receipt_no;
                clearCart();
                if (currentStudent) {
                    currentStudent.raw_balance -= parseFloat(data.total.replace(/,/g, ''));
                    document.getElementById('student-balance').textContent = 'Balance: KES ' + currentStudent.raw_balance.toFixed(2);
                }
            } else {
                msg.className = 'mt-3 text-sm text-red-600 font-bold';
                msg.textContent = data.message;
            }
        })
        .catch(e => {
            msg.className = 'mt-3 text-sm text-red-600';
            msg.textContent = 'Network error: ' + e.message;
        });
    }
</script>
@endsection
