<?php

namespace App\Http\Controllers;

use App\Jobs\SendPanicAlert;
use App\Models\{ApprovedPickup, PanicAlert, PickupLog, Student, User, Visitor};
use Illuminate\Http\Request;

class GuardConsoleController extends Controller
{
    /**
     * Main console — full-screen app.
     */
    public function index()
    {
        if (!session('guard_id')) {
            return redirect('/login');
        }
        return view('guard.console');
    }

    /**
     * Live stats — polled every 5 seconds.
     */
    public function stats()
    {
        if (!session('guard_id')) return response()->json(['error' => 'not auth'], 401);

        return response()->json([
            'inside'      => Visitor::where('status', 'checked_in')->count(),
            'today_in'    => Visitor::whereDate('checked_in_at', today())->count(),
            'today_out'   => Visitor::whereDate('checked_out_at', today())->count(),
            'pickups'     => PickupLog::whereDate('created_at', today())->count(),
            'denied'      => PickupLog::whereDate('created_at', today())->where('result', 'denied')->count(),
            'open_alerts' => PanicAlert::whereNull('resolved_at')->count(),
            'time'        => now()->format('H:i:s'),
        ]);
    }

    /**
     * Recent activity feed — polled every 5 seconds.
     */
    public function recent()
    {
        if (!session('guard_id')) return response()->json(['error' => 'not auth'], 401);

        // Merge pickups + visitors, latest first
        $pickups = PickupLog::with('student')->latest()->take(10)->get()->map(fn($l) => [
            'at'      => $l->created_at->format('H:i'),
            'icon'    => $l->result === 'verified' ? '✓' : '✕',
            'color'   => $l->result === 'verified' ? 'green' : 'red',
            'title'   => $l->student->name ?? 'Unknown',
            'detail'  => $l->picker_name . ' · ' . ($l->relationship ?? ''),
            'type'    => 'pickup',
        ]);

        $visitors = Visitor::latest()->take(10)->get()->map(fn($v) => [
            'at'      => $v->created_at->format('H:i'),
            'icon'    => $v->status === 'checked_in' ? '→' : ($v->status === 'checked_out' ? '←' : '⏳'),
            'color'   => $v->status === 'checked_in' ? 'blue' : ($v->status === 'checked_out' ? 'gray' : 'yellow'),
            'title'   => $v->name,
            'detail'  => $v->purpose . ($v->badge_no ? ' · ' . $v->badge_no : ''),
            'type'    => 'visitor',
        ]);

        $merged = $pickups->merge($visitors)->sortByDesc('at')->take(15)->values();

        return response()->json(['feed' => $merged]);
    }

    /**
     * Quick visitor check-in — inline, no form page.
     */
    public function visitorCheckIn(Request $request)
    {
        if (!session('guard_id')) return response()->json(['error' => 'not auth'], 401);

        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'required|string|max:20',
            'purpose' => 'required|string|max:200',
        ]);

        $badge = 'V-' . str_pad((string) (Visitor::whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);

$photoPath = $request->hasFile("photo") ? $request->file("photo")->store("visitors", "public") : null;
        Visitor::create([
            
            "photo" => $photoPath,
            "name"  => $data["name"],
            'phone'         => $data['phone'],
            'purpose'       => $data['purpose'],
            'status'        => 'checked_in',
            'checked_in_at' => now(),
            'badge_no'      => $badge,
        ]);

        return response()->json([
            'status'  => 'ok',
            'badge'   => $badge,
            'message' => "{$visitor->name} checked in — Badge {$badge}",
        ]);
    }

    /**
     * List currently inside (for quick check-out).
     */
    public function insideList()
    {
        if (!session('guard_id')) return response()->json(['error' => 'not auth'], 401);

        $inside = Visitor::where('status', 'checked_in')->latest('checked_in_at')->take(30)->get();

        return response()->json([
            'inside' => $inside->map(fn($v) => [
                'id'      => $v->id,
                'name'    => $v->name,
                'badge'   => $v->badge_no,
                'purpose' => $v->purpose,
                'since'   => $v->checked_in_at?->format('H:i'),
            ]),
        ]);
    }

    /**
     * Check-out a visitor by ID.
     */
    public function visitorCheckOut(Request $request)
    {
        if (!session('guard_id')) return response()->json(['error' => 'not auth'], 401);

        $data = $request->validate(['visitor_id' => 'required|exists:visitors,id']);
        $v = Visitor::find($data['visitor_id']);
        if (!$v || $v->status !== 'checked_in') {
            return response()->json(['error' => 'Not checked in'], 400);
        }
        $v->update(['status' => 'checked_out', 'checked_out_at' => now()]);
        return response()->json(['status' => 'ok', 'message' => "{$v->name} checked out"]);
    }
}
