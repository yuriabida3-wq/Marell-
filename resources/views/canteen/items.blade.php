@extends('layouts.app')
@section('content')
<div class="p-4 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">🍔 Canteen Items</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded-xl mb-3">{{ session('success') }}</div>
    @endif

    {{-- Add new item --}}
    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <h2 class="font-bold mb-3">Add New Item</h2>
        <form method="POST" action="{{ route('principal.canteen-items.store') }}" class="grid grid-cols-2 md:grid-cols-6 gap-2">
            @csrf
            <input name="name" placeholder="Item name" required class="border rounded-xl p-2 col-span-2">
            <input name="category" placeholder="Category" required class="border rounded-xl p-2">
            <input name="price" type="number" min="1" placeholder="Price" required class="border rounded-xl p-2">
            <input name="stock" type="number" min="0" placeholder="Stock (0=∞)" class="border rounded-xl p-2">
            <button type="submit" class="bg-navy text-white rounded-xl px-4 py-2 font-bold">Add</button>
        </form>
    </div>

    {{-- Items list --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-navy text-white">
                <tr>
                    <th class="p-3">Name</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Price</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Active</th>
                    <th class="p-3">Blockable</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr class="border-b">
                    <td class="p-3 font-semibold">{{ $item->name }}</td>
                    <td class="p-3">{{ $item->category }}</td>
                    <td class="p-3">KES {{ number_format($item->price, 0) }}</td>
                    <td class="p-3">{{ $item->stock > 0 ? $item->stock : '∞' }}</td>
                    <td class="p-3">{!! $item->active ? '<span class="text-green-600">✅</span>' : '<span class="text-red-500">❌</span>' !!}</td>
                    <td class="p-3">{!! $item->blockable ? '🔒' : '🔓' !!}</td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('principal.canteen-items.toggle', $item) }}" class="inline">
                            @csrf
                            <button class="text-xs underline">{{ $item->active ? 'Disable' : 'Enable' }}</button>
                        </form>
                        <form method="POST" action="{{ route('principal.canteen-items.destroy', $item) }}" class="inline ml-2"
                              onsubmit="return confirm('Delete {{ $item->name }}?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-4 text-center text-gray-400">No items yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-500 mt-3">
        💡 Tip: Guests see this menu at <a href="{{ route('canteen.menu') }}" class="underline">/canteen/menu</a>. Parents can block items/categories from the Parent Portal.
    </p>
</div>
@endsection
