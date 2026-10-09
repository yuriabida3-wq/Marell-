<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CanteenItem;
use App\Models\Student;
use App\Models\WalletTransaction;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;

class CanteenItemController extends Controller
{
    /** Guard-facing: checkout cart */
    public function checkout(Request $request)
    {
        if (!session('guard_id') && !auth()->check()) {
            return response()->json(['error' => 'Not authorized'], 401);
        }

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'item_ids'   => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:canteen_items,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1|max:20',
        ]);

        $student = Student::findOrFail($data['student_id']);
        $itemIds = $data['item_ids'];
        $quantities = $data['quantities'] ?? array_fill(0, count($itemIds), 1);

        try {
            $result = DB::transaction(function () use ($student, $itemIds, $quantities) {
                $locked = Student::where('id', $student->id)->lockForUpdate()->first();

                // Load items
                $items = CanteenItem::whereIn('id', $itemIds)->get()->keyBy('id');

                // Build cart lines
                $lines = [];
                $total = 0;
                $names = [];

                foreach ($itemIds as $idx => $id) {
                    if (!isset($items[$id])) continue;
                    $item = $items[$id];
                    $qty = (int) ($quantities[$idx] ?? 1);

                    // --- Blocked item check ---
                    $blockedItems = $locked->blocked_items ?? [];
                    $blockedCats = $locked->blocked_categories ?? [];

                    if ($item->blockable && in_array(strtolower($item->name), array_map('strtolower', $blockedItems))) {
                        throw new \RuntimeException("BLOCKED: {$item->name} is blocked by parent.");
                    }
                    if ($item->blockable && in_array(strtolower($item->category), array_map('strtolower', $blockedCats))) {
                        throw new \RuntimeException("BLOCKED: All {$item->category} items are blocked by parent.");
                    }

                    // --- Stock check ---
                    if ($item->stock > 0 && $item->stock < $qty) {
                        throw new \RuntimeException("Out of stock: {$item->name} (only {$item->stock} left)");
                    }

                    $lineTotal = (float) $item->price * $qty;
                    $total += $lineTotal;
                    $names[] = $qty > 1 ? "{$qty}x {$item->name}" : $item->name;

                    $lines[] = [
                        'item'  => $item,
                        'qty'   => $qty,
                        'total' => $lineTotal,
                    ];
                }

                // --- Daily limit check ---
                $dailySpend = WalletTransaction::where('student_id', $locked->id)
                    ->where('category', 'canteen')
                    ->where('type', 'spend')
                    ->whereDate('created_at', today())
                    ->sum('amount');

                $dailyLimit = (float) ($locked->daily_limit ?? 0);
                if ($dailyLimit > 0 && ($dailySpend + $total) > $dailyLimit) {
                    throw new \RuntimeException("Daily limit KES " . number_format($dailyLimit, 2) . " | Spent today KES " . number_format((float) $dailySpend, 2));
                }

                // --- Balance check ---
                if ($total > (float) $locked->wallet_balance) {
                    throw new \RuntimeException("Insufficient wallet balance (KES " . number_format((float) $locked->wallet_balance, 2) . ")");
                }

                // --- Deduct + log ---
                $newBalance = (float) $locked->wallet_balance - $total;
                $locked->update(['wallet_balance' => $newBalance]);

                $staffName = session('guard_name') ?? (auth()->user()->name ?? 'Canteen');

                $txn = WalletTransaction::create([
                    'student_id'    => $locked->id,
                    'type'          => 'spend',
                    'amount'        => $total,
                    'balance_after' => $newBalance,
                    'category'      => 'canteen',
                    'status'        => 'completed',
                    'description'   => implode(', ', $names),
                    'recorded_by'   => $staffName,
                    'staff_id'      => session('guard_id') ?? auth()->id(),
                    'ip'            => request()->ip(),
                ]);

                // --- Decrement stock ---
                foreach ($lines as $line) {
                    if ($line['item']->stock > 0) {
                        $line['item']->decrement('stock', $line['qty']);
                    }
                }

                return ['txn' => $txn, 'total' => $total, 'new_balance' => $newBalance, 'names' => $names];
            });

            // --- SMS to parent ---
            try {
                app(SmsService::class)->send(
                    $student->parent_phone,
                    "MARELL CANTEEN\n{$student->name} bought: " . implode(', ', $result['names']) .
                    "\nTotal: KES " . number_format($result['total'], 2) .
                    "\nNew balance: KES " . number_format($result['new_balance'], 2)
                );
            } catch (\Throwable $e) {}

            // --- Low balance alert ---
            try {
                $fresh = $student->fresh();
                $threshold = (float) ($student->low_balance_alert ?? 200);
                if ($threshold > 0 && (float) $fresh->wallet_balance < $threshold && (float) $fresh->wallet_balance >= 0) {
                    app(SmsService::class)->send(
                        $student->parent_phone,
                        "MARELL ALERT\n{$student->name} wallet is LOW: KES " . number_format((float) $fresh->wallet_balance, 2) . ".\nPlease top up."
                    );
                }
            } catch (\Throwable $e) {}

            return response()->json([
                'status'      => 'ok',
                'message'     => '✅ ' . implode(', ', $result['names']),
                'total'       => number_format($result['total'], 2),
                'new_balance' => number_format($result['new_balance'], 2),
                'receipt_no'  => 'CANT-' . str_pad((string) $result['txn']->id, 6, '0', STR_PAD_LEFT),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => '❌ ' . $e->getMessage(),
            ], 200);
        }
    }

    /** Principal-facing: item management list */
    public function index()
    {
        $items = CanteenItem::orderBy('category')->orderBy('name')->get();
        return view('canteen.items', compact('items'));
    }

    /** Principal-facing: create item */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'category'  => 'required|string|max:50',
            'price'     => 'required|numeric|min:1|max:100000',
            'stock'     => 'nullable|integer|min:0',
            'blockable' => 'nullable|boolean',
            'active'    => 'nullable|boolean',
        ]);

        CanteenItem::create([
            'name'      => $data['name'],
            'category'  => strtolower($data['category']),
            'price'     => $data['price'],
            'stock'     => $data['stock'] ?? 0,
            'blockable' => $request->boolean('blockable', true),
            'active'    => $request->boolean('active', true),
        ]);

        return back()->with('success', "Item '{$data['name']}' added.");
    }

    /** Principal-facing: update item */
    public function update(Request $request, CanteenItem $item)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'category'  => 'required|string|max:50',
            'price'     => 'required|numeric|min:1|max:100000',
            'stock'     => 'nullable|integer|min:0',
            'blockable' => 'nullable|boolean',
            'active'    => 'nullable|boolean',
        ]);

        $item->update([
            'name'      => $data['name'],
            'category'  => strtolower($data['category']),
            'price'     => $data['price'],
            'stock'     => $data['stock'] ?? 0,
            'blockable' => $request->boolean('blockable', true),
            'active'    => $request->boolean('active', true),
        ]);

        return back()->with('success', "Item updated.");
    }

    /** Principal-facing: toggle active */
    public function toggle(CanteenItem $item)
    {
        $item->update(['active' => !$item->active]);
        return back()->with('success', "Item " . ($item->active ? 'enabled' : 'disabled') . ".");
    }

    /** Principal-facing: delete item */
    public function destroy(CanteenItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item deleted.');
    }
}
