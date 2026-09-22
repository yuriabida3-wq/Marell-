<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StaffAttendance;
use Carbon\Carbon;
class StaffAttendanceController extends Controller {
    public function scan(Request $request) {
        $request->validate(["qr_data" => "required|string"]);
        $user = User::where("email", $request->qr_data)->first();
        if (!$user) return back()->with("error", "Staff member not found.");
        $today = Carbon::today();
        $lastLog = StaffAttendance::where("user_id", $user->id)->whereDate("scanned_at", $today)->latest()->first();
        $action = ($lastLog && $lastLog->action === "in") ? "out" : "in";
        StaffAttendance::create(["user_id" => $user->id, "action" => $action, "scanned_at" => now()]);
        return back()->with("success", "Staff {$user->name} checked {$action} at " . now()->format("h:i A"));
    }
    public function index() {
        $logs = StaffAttendance::with("user")->latest()->paginate(50);
        return view("staff.attendance", compact("logs"));
    }
    public function qrcodes() {
        $staff = User::all();
        return view("staff.qrcodes", compact("staff"));
    }
}
