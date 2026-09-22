<?php

namespace App\Http\Controllers;

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
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:20|max:50000',
        ]);

        $student = Student::where('id', $data['student_id'])
            ->where('parent_phone', $phone)
            ->firstOrFail();

        // Create pending manual load — in real M-Pesa this would trigger STK push
        // For demo, directly credit
        try {
            $txn = $student->topUpWallet(
                (float) $data['amount'],
                'MANUAL-' . strtoupper(uniqid()),
                'load',
                'Wallet top-up by parent'
            );

            // Send SMS
            try {
                app(SmsService::class)->send(
                    $student->parent_phone,
                    "MARELL WALLET\nLoaded KES " . number_format($data['amount'], 2) .
                    " for {$student->name}.\nNew balance: KES " . number_format($student->fresh()->wallet_balance, 2)
                );
            } catch (\Throwable $e) {}

            return back()->with('success', "Wallet loaded KES " . number_format($data['amount'], 2) . ". New balance: KES " . number_format($student->fresh()->wallet_balance, 2));
        } catch (\Throwable $e) {
            return back()->with('error', 'Top-up failed: ' . $e->getMessage());
        }
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
                'id'      => $student->id,
                'name'    => $student->name,
                'adm_no'  => $student->adm_no,
                'class'   => $student->class . ' ' . $student->stream,
                'balance' => number_format((float) $student->wallet_balance, 2),
                'raw_balance' => (float) $student->wallet_balance,
            ],
        ]);
    }

    public function canteenSell(Request $request)
    {
        if (!session('guard_id') && !auth()->check()) {
            return response()->json(['error' => 'Not authorized'], 401);
        }

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:1|max:10000',
            'items'      => 'nullable|string|max:500',
        ]);

        $student = Student::findOrFail($data['student_id']);

        try {
            DB::transaction(function () use ($student, $data, &$txn) {
                $locked = Student::where('id', $student->id)->lockForUpdate()->first();

                if ((float) $data['amount'] > (float) $locked->wallet_balance) {
                    throw new \RuntimeException("Insufficient wallet balance (KES " . number_format((float) $locked->wallet_balance, 2) . ")");
                }

                $staffName = session('guard_name') ?? (auth()->user()->name ?? 'Canteen');

                $newBalance = (float) $locked->wallet_balance - (float) $data['amount'];
                $locked->update(['wallet_balance' => $newBalance]);

                $txn = WalletTransaction::create([
                    'student_id'    => $locked->id,
                    'type'          => 'spend',
                    'amount'        => $data['amount'],
                    'balance_after' => $newBalance,
                    'category'      => 'canteen',
                    'description'   => $data['items'] ?? 'Canteen purchase',
                    'recorded_by'   => $staffName,
                    'staff_id'      => session('guard_id') ?? auth()->id(),
                    'ip'            => request()->ip(),
                ]);
            });

            // SMS to parent (stubbed)
            try {
                app(SmsService::class)->send(
                    $student->parent_phone,
                    "MARELL CANTEEN\n{$student->name} spent KES " . number_format($data['amount'], 2) .
                    ".\nNew balance: KES " . number_format($student->fresh()->wallet_balance, 2)
                );
            } catch (\Throwable $e) {}

            return response()->json([
                'status'        => 'ok',
                'message'       => '✅ Purchase recorded',
                'new_balance'   => number_format($student->fresh()->wallet_balance, 2),
                'receipt_no'    => 'CANT-' . str_pad((string) $txn->id, 6, '0', STR_PAD_LEFT),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => '❌ ' . $e->getMessage(),
            ], 200);
        }
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
}
