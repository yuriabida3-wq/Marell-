<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Visitor;
use App\Services\SmsService;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    // ============== GATE DASHBOARD (for security guard) ==============
    public function gate()
    {
        $inside    = Visitor::currentlyInside()->latest('checked_in_at')->get();
        $expected  = Visitor::where('status', 'expected')->orderBy('created_at')->get();
        $todayIn   = Visitor::whereDate('checked_in_at', today())->count();
        $todayOut  = Visitor::whereDate('checked_out_at', today())->count();

        return view('visitors.gate', compact('inside', 'expected', 'todayIn', 'todayOut'));
    }

    // ============== CHECK-IN ==============
    public function checkInForm()
    {
        return view('visitors.check-in');
    }

    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:120',
            'phone'         => 'required|string|max:20',
            'id_number'     => 'nullable|string|max:30',
            'purpose'       => 'required|string|max:200',
            'host_name'     => 'nullable|string|max:120',
            'host_type'     => 'nullable|in:staff,student,admin',
            'student_adm'   => 'nullable|string|max:30',
            'vehicle_plate' => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:500',
        ]);

        // Auto-generate badge number
        $badgeNo = 'V-' . str_pad((string) (Visitor::whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);

        $data['status']         = 'checked_in';
        $data['checked_in_at']  = now();
        $data['badge_no']       = $badgeNo;

        $visitor = Visitor::create($data);

        // Notify host via SMS
        $this->notifyHost($visitor);

        return redirect()->route('visitors.gate')
            ->with('success', "{$visitor->name} checked in. Badge: {$badgeNo}");
    }

    // ============== CHECK-OUT ==============
    public function checkOut(Visitor $visitor)
    {
        if ($visitor->status !== 'checked_in') {
            return back()->with('error', 'Visitor is not currently checked in.');
        }

        $visitor->update([
            'status'          => 'checked_out',
            'checked_out_at'  => now(),
        ]);

        return back()->with('success', "{$visitor->name} checked out. Badge {$visitor->badge_no} returned.");
    }

    // ============== FULL LIST ==============
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Visitor::latest();

        if ($q) {
            $query->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('badge_no', 'like', "%{$q}%")
                  ->orWhere('vehicle_plate', 'like', "%{$q}%");
            });
        }
        if ($status) $query->where('status', $status);
        if ($from)   $query->whereDate('created_at', '>=', $from);
        if ($to)     $query->whereDate('created_at', '<=', $to);

        $visitors = $query->paginate(30)->withQueryString();

        return view('visitors.index', compact('visitors', 'q', 'status', 'from', 'to'));
    }

    public function show(Visitor $visitor)
    {
        return view('visitors.show', compact('visitor'));
    }

    public function destroy(Visitor $visitor)
    {
        $visitor->delete();
        return redirect()->route('visitors.index')->with('success', 'Visitor log deleted.');
    }

    // ============== EXPECTED VISITORS ==============
    public function preRegisterForm()
    {
        return view('visitors.pre-register');
    }

    public function preRegister(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'phone'       => 'required|string|max:20',
            'purpose'     => 'required|string|max:200',
            'host_name'   => 'nullable|string|max:120',
            'host_type'   => 'nullable|in:staff,student,admin',
            'student_adm' => 'nullable|string|max:30',
        ]);

        $data['status'] = 'expected';

        Visitor::create($data);

        return back()->with('success', 'Visitor pre-registered. They will be expected at the gate.');
    }

    protected function notifyHost(Visitor $visitor): void
    {
        try {
            $sms = app(SmsService::class);

            // If visiting a student, notify parent
            if ($visitor->student_adm) {
                $student = Student::where('adm_no', $visitor->student_adm)->first();
                if ($student && $student->parent_phone) {
                    $msg = "MARELL ACADEMY\n{$visitor->name} ({$visitor->phone}) is at the gate to see {$student->name}.\nPurpose: {$visitor->purpose}\nBadge: {$visitor->badge_no}";
                    $sms->send($student->parent_phone, $msg);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Visitor notification failed: ' . $e->getMessage());
        }
    }
}
