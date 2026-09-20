<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;

class AdmissionAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = Admission::latest();
        if ($status) $query->where('status', $status);
        $applications = $query->paginate(20)->withQueryString();
        $counts = [
            'new'       => Admission::where('status', 'new')->count(),
            'contacted' => Admission::where('status', 'contacted')->count(),
            'admitted'  => Admission::where('status', 'admitted')->count(),
            'rejected'  => Admission::where('status', 'rejected')->count(),
        ];
        return view('principal.admissions.index', compact('applications', 'status', 'counts'));
    }

    public function show(Admission $admission)
    {
        return view('principal.admissions.show', compact('admission'));
    }

    public function updateStatus(Request $request, Admission $admission)
    {
        $data = $request->validate([
            'status' => 'required|in:new,contacted,admitted,rejected',
        ]);
        $admission->update(['status' => $data['status']]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(Admission $admission)
    {
        $admission->delete();
        return redirect()->route('principal.admissions.index')->with('success', 'Application deleted.');
    }
}
