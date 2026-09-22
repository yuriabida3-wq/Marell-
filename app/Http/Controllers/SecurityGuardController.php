<?php

namespace App\Http\Controllers;

use App\Jobs\SendPanicAlert;
use App\Models\ApprovedPickup;
use App\Models\PanicAlert;
use App\Models\PickupLog;
use App\Models\Student;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecurityGuardController extends Controller
{
    // ============== LOGIN ==============
    public function loginForm()
    {
        if (session('guard_id')) {
            return redirect()->route('security.dashboard');
        }
        return view('security.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'pin'   => 'required|string|min:4|max:8',
        ]);

        $phone = $this->normalize($data['phone']);

        $user = User::where('phone', $phone)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['security', 'principal', 'dos', 'bursar']))
            ->where('active', true)
            ->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'No security account found.'])->withInput();
        }

        if (!$user->pin || !Hash::check($data['pin'], $user->pin)) {
            return back()->withErrors(['pin' => 'Invalid PIN.'])->withInput();
        }

        session([
            'guard_id'   => $user->id,
            'guard_name' => $user->name,
        ]);

        return redirect()->route('security.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['guard_id', 'guard_name']);
        return redirect()->route('security.login');
    }

    // ============== DASHBOARD ==============
    public function dashboard()
    {
        if (!session('guard_id')) return redirect()->route('security.login');

        $inside = Visitor::where('status', 'checked_in')->count();
        $todayReleases = PickupLog::whereDate('created_at', today())->count();
        $verified = PickupLog::whereDate('created_at', today())->where('result', 'verified')->count();
        $denied   = PickupLog::whereDate('created_at', today())->where('result', 'denied')->count();
        $openAlerts = PanicAlert::whereNull('resolved_at')->count();

        return view('security.dashboard', compact('inside', 'todayReleases', 'verified', 'denied', 'openAlerts'));
    }

    // ============== SCAN PICKUP QR ==============
    public function scan()
    {
        if (!session('guard_id')) return redirect()->route('security.login');
        return view('security.scan');
    }

    public function verify(Request $request)
    {
        if (!session('guard_id')) return redirect()->route('security.login');

        $data = $request->validate(['token' => 'required|string|max:200']);

        $token = trim($data['token']);

        // If URL was scanned, extract the token
        if (str_contains($token, '/')) {
            $parts = explode('/', rtrim($token, '/'));
            $token = end($parts);
        }

        $pickup = ApprovedPickup::with('student')->where('qr_token', $token)->first();

        if (!$pickup) {
            PickupLog::create([
                'student_id' => 0,
                'picker_name' => 'Unknown',
                'result' => 'denied',
                'reason' => 'Invalid QR token',
                'guard_id' => session('guard_id'),
                'guard_name' => session('guard_name'),
                'ip' => $request->ip(),
            ]);
            return response()->json(['status' => 'invalid', 'message' => '❌ This QR is not valid. Do not release.']);
        }

        if (!$pickup->active) {
            PickupLog::create([
                'approved_pickup_id' => $pickup->id,
                'student_id' => $pickup->student_id,
                'picker_name' => $pickup->name,
                'picker_phone' => $pickup->phone,
                'relationship' => $pickup->relationship,
                'result' => 'denied',
                'reason' => 'Pickup pass is inactive',
                'guard_id' => session('guard_id'),
                'guard_name' => session('guard_name'),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'status' => 'denied',
                'message' => '🚫 This pickup pass is INACTIVE. Do not release the child.',
            ]);
        }

        // All good — log verified
        PickupLog::create([
            'approved_pickup_id' => $pickup->id,
            'student_id' => $pickup->student_id,
            'picker_name' => $pickup->name,
            'picker_phone' => $pickup->phone,
            'relationship' => $pickup->relationship,
            'result' => 'verified',
            'guard_id' => session('guard_id'),
            'guard_name' => session('guard_name'),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'verified',
            'student_name'  => $pickup->student->name,
            'student_adm'   => $pickup->student->adm_no,
            'student_class' => $pickup->student->class . ' ' . $pickup->student->stream,
            'picker_name'   => $pickup->name,
            'picker_phone'  => $pickup->phone,
            'relationship'  => $pickup->relationship,
            'photo'         => $pickup->photo ? asset('storage/' . $pickup->photo) : null,
            'message' => '✅ VERIFIED — You may release the child.',
        ]);
    }

    // ============== PANIC BUTTON ==============
    public function panic(Request $request)
    {
        if (!session('guard_id')) return response()->json(['error' => 'Not logged in'], 401);

        $data = $request->validate([
            'location' => 'nullable|string|max:120',
            'note'     => 'nullable|string|max:500',
        ]);

        $guard = User::find(session('guard_id'));

        $alert = PanicAlert::create([
            'user_id'   => $guard->id,
            'user_name' => $guard->name,
            'location'  => $data['location'] ?? 'Main Gate',
            'note'      => $data['note'] ?? null,
            'ip'        => $request->ip(),
        ]);

        SendPanicAlert::dispatch($alert)->onQueue('default');

        return response()->json(['ok' => true, 'alert_id' => $alert->id, 'message' => 'Alert sent to Director']);
    }

    // ============== LOGS ==============
    public function logs()
    {
        if (!session('guard_id')) return redirect()->route('security.login');
        $logs = PickupLog::with('student')->latest()->paginate(30);
        return view('security.logs', compact('logs'));
    }

    protected function normalize(string $phone): string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) $p = '254' . substr($p, 1);
        if (strlen($p) === 9) $p = '254' . $p;
        return $p;
    }
}
