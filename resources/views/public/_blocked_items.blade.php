@php
    $canteenItems = \App\Models\CanteenItem::where('active', true)->where('blockable', true)->orderBy('category')->orderBy('name')->get();
    $canteenCategories = $canteenItems->pluck('category')->unique()->values();
    $blockedItems = $selected->blocked_items ?? [];
    $blockedCategories = $selected->blocked_categories ?? [];
@endphp

<div class="bg-white rounded-2xl shadow p-4 mt-4">
    <h3 class="font-bold text-navy mb-2">🚫 Block Items for {{ $selected->name }}</h3>
    <p class="text-xs text-gray-500 mb-3">Tick items to prevent your child from buying them at the canteen.</p>

    @if(session('success') && str_contains(session('success'), 'Blocked'))
        <div class="bg-green-100 text-green-800 p-2 rounded-xl mb-3 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('parent.wallet.blocked') }}">
        @csrf
        <input type="hidden" name="student_id" value="{{ $selected->id }}">

        <div class="mb-4">
            <p class="text-sm font-bold mb-2">Block Whole Categories</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($canteenCategories as $cat)
                    <label class="flex items-center gap-2 p-2 border rounded-xl cursor-pointer">
                        <input type="checkbox" name="blocked_categories[]" value="{{ $cat }}"
                               {{ in_array($cat, $blockedCategories) ? 'checked' : '' }}
                               class="w-5 h-5">
                        <span class="text-sm capitalize">{{ $cat }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mb-4">
            <p class="text-sm font-bold mb-2">Block Specific Items</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($canteenItems as $item)
                    <label class="flex items-center gap-2 p-2 border rounded-xl cursor-pointer">
                        <input type="checkbox" name="blocked_items[]" value="{{ $item->name }}"
                               {{ in_array($item->name, $blockedItems) ? 'checked' : '' }}
                               class="w-5 h-5">
                        <span class="text-sm">{{ $item->name }} <span class="text-gray-400">KES {{ number_format($item->price, 0) }}</span></span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full min-h-[44px] rounded-xl bg-navy text-white font-bold">
            Save Blocked Items
        </button>
    </form>
</div>
