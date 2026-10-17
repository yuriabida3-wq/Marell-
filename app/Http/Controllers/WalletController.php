<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CanteenItem;
use App\Models\Student;
use App\Models\WalletTransaction;
use App\Services\MpesaService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    // ============== PARENT: WALLET DASHBOARD ==============
    public function parentIndex(Request $request)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $children = Student::where('parent_phone', $phone)->get();
        if ($children->isEmpty()) {
            session()->forget('parent_phone');
            return redirect()->route('parent.login');
        }

        $childId = $request->get('child', $children->first()->id);
        $selected = $children->firstWhere('id', (int) $childId) ?? $children->first();

        $transactions = WalletTransaction::where('student_id', $selected->id)
            ->latest()->take(20)->get();

        return view('public.parent-wallet', compact('children', 'selected', 'transactions'));
    }

    public function parentTopUp(Request $request)
    {
        if (!\App\Support\PaymentSafety::paymentsAllowed()) {
            return back()->with('error', \App\Support\PaymentSafety::blockReason());
        }

        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:20|max:50000',
        ]);

        $student = Student::where('id', $data['student_id'])
            ->where('parent_phone', $phone)
            ->firstOrFail();

        // CP-27: per-phone limit on wallet top-up initiation.
        $rlKey = 'wallet-topup:' . $phone;
        if (RateLimiter::tooManyAttempts($rlKey, 5)) {
            $secs = RateLimiter::availableIn($rlKey);
            return back()->with('error', "Too many top-up attempts. Try again in {$secs} seconds.");
        }
        RateLimiter::hit($rlKey, 900);

        $txn = WalletTransaction::create([
            'student_id'       => $student->id,
            'type'             => 'topup',
            'amount'           => $data['amount'],
            'initiated_amount' => $data['amount'],
            'balance_after' => $student->wallet_balance,
            'category'      => 'mpesa_topup',
            'status'        => 'pending',
            'phone'         => $student->parent_phone,
            'description'   => 'M-Pesa wallet top-up (pending)',
            'reference'     => 'PEND-' . strtoupper(uniqid()),
        ]);

        try {
            $response = app(\App\Services\MpesaService::class)->stkPush(
                \App\Services\MpesaService::normalizePhone($student->parent_phone),
                (float) $data['amount'],
                'WALLET-' . $txn->id,
                'Wallet Top-up'
            );
        } catch (\Throwable $e) {
            $txn->update(['status' => 'under_reconciliation', 'description' => 'STK error: ' . $e->getMessage() . ' — held for review']);
            return back()->with('error', 'Could not reach M-Pesa. Try again.');
        }

        if (!empty($response['CheckoutRequestID'])) {
            $txn->update(['checkout_request_id' => $response['CheckoutRequestID']]);
            return back()->with('success', 'M-Pesa prompt sent to ' . $student->parent_phone . '. Enter your PIN.');
        }

        $err = $response['errorMessage'] ?? ($response['error'] ?? 'Unknown error');
        $txn->update(['status' => 'under_reconciliation', 'description' => 'STK rejected: ' . $err . ' — held for review']);
        return back()->with('error', 'M-Pesa declined: ' . $err);
    }

    public function parentSettings(Request $request)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $data = $request->validate([
            'student_id'             => 'required|exists:students,id',
            'auto_reload_enabled'    => 'nullable|boolean',
            'auto_reload_threshold'  => 'nullable|numeric|min:0',
            'auto_reload_amount'     => 'nullable|numeric|min:20',
            'auto_reload_phone'      => 'nullable|string|max:20',
        ]);

        $student = Student::where('id', $data['student_id'])
            ->where('parent_phone', $phone)
            ->firstOrFail();

        $student->update([
            'auto_reload_enabled'   => !empty($data['auto_reload_enabled']),
            'auto_reload_threshold' => $data['auto_reload_threshold'] ?? 0,
            'auto_reload_amount'    => $data['auto_reload_amount'] ?? 0,
            'auto_reload_phone'     => $data['auto_reload_phone'] ?? $student->parent_phone,
        ]);

        return back()->with('success', 'Auto-reload settings saved.');
    }

    // ============== CANTEEN: SCAN + SELL ==============
    public function canteen()
    {
        if (!session('guard_id') && !auth()->check()) {
            return redirect()->route('security.login');
        }

        $items = CanteenItem::where('active', true)->orderBy('category')->orderBy('name')->get();
        $todaySpent = WalletTransaction::where('type', 'spend')
            ->whereDate('created_at', today())->sum('amount');
        $todayCount = WalletTransaction::where('type', 'spend')
            ->whereDate('created_at', today())->count();

        return view('canteen.index', compact('items', 'todaySpent', 'todayCount'));
    }

    public function canteenLookup(Request $request)
    {
        $data = $request->validate(['token' => 'required|string|max:200']);

        $token = trim($data['token']);
        if (str_contains($token, '/')) {
            $parts = explode('/', rtrim($token, '/'));
            $token = end($parts);
        }

        $student = Student::where('qr_token', $token)->first();

        if (!$student) {
            return response()->json(['status' => 'invalid', 'message' => '❌ Student QR not recognized.']);
        }

        return response()->json([
            'status'  => 'ok',
            'student' => [
                'id'                 => $student->id,
                'name'               => $student->name,
                'adm_no'             => $student->adm_no,
                'class'              => $student->class . ' ' . $student->stream,
                'balance'            => number_format((float) $student->wallet_balance, 2),
                'raw_balance'        => (float) $student->wallet_balance,
                'daily_limit'        => (float) $student->daily_limit,
                'blocked_items'      => $student->blocked_items ?? [],
                'blocked_categories' => $student->blocked_categories ?? [],
            ],
        ]);
    }

    public function canteenSell(Request $request)
    {
        // CP-18: deprecated. This endpoint deducted an arbitrary free-text
        // amount and bypassed blocked_items, blocked_categories, and stock.
        // All canteen sales must go through CanteenItemController@checkout.
        \Illuminate\Support\Facades\Log::warning('Deprecated /canteen/sell called', [
            'ip'        => $request->ip(),
            'has_items' => $request->filled('items'),
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'This endpoint is no longer supported. Please use the item menu.',
        ], 410);
    }

    public function canteenLogs()
    {
        if (!session('guard_id') && !auth()->check()) {
            return redirect()->route('security.login');
        }

        $logs = WalletTransaction::with('student')
            ->where('type', 'spend')
            ->whereDate('created_at', today())
            ->latest()
            ->paginate(40);

        return view('canteen.logs', compact('logs'));
    }

    // ============== BURSAR: WALLET MANAGEMENT ==============
    public function admin()
    {
        $students = Student::orderBy('class')->orderBy('name')->paginate(30);
        $totalWallets = Student::sum('wallet_balance');
        $todayLoad = WalletTransaction::where('type', 'load')->whereDate('created_at', today())->sum('amount');
        $todaySpend = WalletTransaction::where('type', 'spend')->whereDate('created_at', today())->sum('amount');

        return view('principal.wallet.index', compact('students', 'totalWallets', 'todayLoad', 'todaySpend'));
    }

    public function adminLoad(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:1|max:100000',
            'notes'      => 'nullable|string|max:300',
        ]);

        $student = Student::findOrFail($data['student_id']);
        $student->topUpWallet(
            (float) $data['amount'],
            'ADMIN-' . strtoupper(uniqid()),
            'load',
            $data['notes'] ?? 'Admin load'
        );

        AuditLog::log('wallet.admin_load', $student, [], [
            'amount'      => (float) $data['amount'],
            'new_balance' => (float) $student->fresh()->wallet_balance,
        ], $data['notes'] ?? 'Admin load');

        return back()->with('success', "Loaded KES " . number_format($data['amount'], 2) . " for {$student->name}.");
    }

    public function adminSearch(Request $request)
    {
        $q = trim($request->get('q', ''));
        $students = Student::where('name', 'like', "%{$q}%")
            ->orWhere('adm_no', 'like', "%{$q}%")
            ->orWhere('parent_phone', 'like', "%{$q}%")
            ->take(10)
            ->get();

        return response()->json([
            'results' => $students->map(fn($s) => [
                'id'      => $s->id,
                'name'    => $s->name,
                'adm_no'  => $s->adm_no,
                'class'   => $s->class,
                'balance' => number_format((float) $s->wallet_balance, 2),
            ]),
        ]);
    }


    public function canteenReport(Request $request)
    {
        $days = (int) ($request->input('days', 7));
        $from = now()->subDays($days)->startOfDay();
        $to   = now()->endOfDay();

        $base = \App\Models\WalletTransaction::where('category', 'canteen')
            ->where('type', 'spend')
            ->whereBetween('created_at', [$from, $to]);

        $total = (clone $base)->sum('amount');
        $count = (clone $base)->count();
        $avg   = $count > 0 ? $total / $count : 0;

        $daily = (clone $base)->selectRaw('DATE(created_at) as day, SUM(amount) as total, COUNT(*) as txns')
            ->groupBy('day')->orderByDesc('day')->get();

        $topSpenders = \App\Models\WalletTransaction::where('category', 'canteen')
            ->where('type', 'spend')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('student_id, SUM(amount) as total, COUNT(*) as txns')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->limit(15)
            ->with('student')
            ->get();

        return view('wallet.canteen_report', compact('total', 'count', 'avg', 'daily', 'topSpenders', 'days'));
    }



    public function saveBlockedItems(Request $request)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $data = $request->validate([
            'student_id'          => 'required|exists:students,id',
            'blocked_items'       => 'nullable|array',
            'blocked_items.*'     => 'string|max:100',
            'blocked_categories'  => 'nullable|array',
            'blocked_categories.*'=> 'string|max:50',
        ]);

        $student = Student::where('id', $data['student_id'])
            ->where('parent_phone', $phone)
            ->firstOrFail();

        $student->update([
            'blocked_items'      => $data['blocked_items'] ?? [],
            'blocked_categories' => $data['blocked_categories'] ?? [],
        ]);

        return back()->with('success', 'Blocked items updated for ' . $student->name);
    }

}
