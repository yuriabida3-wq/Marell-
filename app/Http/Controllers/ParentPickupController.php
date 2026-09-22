<?php

namespace App\Http\Controllers;

use App\Models\ApprovedPickup;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParentPickupController extends Controller
{
    public function index(Request $request)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $children = Student::where('parent_phone', $phone)->get();
        if ($children->isEmpty()) {
            session()->forget('parent_phone');
            return redirect()->route('parent.login')->with('error', 'No children linked.');
        }

        $childId = $request->get('child', $children->first()->id);
        $selected = $children->firstWhere('id', (int) $childId) ?? $children->first();

        $pickups = ApprovedPickup::where('student_id', $selected->id)
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return view('public.parent-pickups', compact('children', 'selected', 'pickups'));
    }

    public function store(Request $request)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $data = $request->validate([
            'student_id'   => 'required|exists:students,id',
            'name'         => 'required|string|max:120',
            'phone'        => 'required|string|max:20',
            'relationship' => 'required|string|max:60',
            'id_number'    => 'nullable|string|max:30',
        ]);

        // Verify parent owns this student
        $student = Student::where('id', $data['student_id'])
            ->where('parent_phone', $phone)
            ->first();
        if (!$student) return back()->with('error', 'Unauthorized.');

        $data['qr_token'] = Str::random(48);
        $data['active']   = true;

        ApprovedPickup::create($data);

        return back()->with('success', "{$data['name']} added to approved pickups for {$student->name}.");
    }

    public function toggle(ApprovedPickup $pickup)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        // Verify ownership
        if ($pickup->student->parent_phone !== $phone) {
            abort(403);
        }

        $pickup->update(['active' => !$pickup->active]);

        return back()->with('success', $pickup->active ? 'Activated.' : 'Deactivated.');
    }

    public function destroy(ApprovedPickup $pickup)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        if ($pickup->student->parent_phone !== $phone) {
            abort(403);
        }

        $name = $pickup->name;
        $pickup->delete();

        return back()->with('success', "Removed {$name} from approved pickups.");
    }

    public function qrCard(ApprovedPickup $pickup)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        if ($pickup->student->parent_phone !== $phone) {
            abort(403);
        }

        return view('public.pickup-qr-card', compact('pickup'));
    }
}
