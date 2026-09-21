<?php

namespace App\Http\Controllers;

use App\Models\Confession;
use Illuminate\Http\Request;

class ConfessionController extends Controller
{
    public function form() { return view('public.confession'); }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:bursar,teacher,staff,facility,bullying,fees,other',
            'message'  => 'required|string|min:10|max:2000',
            'contact'  => 'nullable|string|max:120',
        ]);
        $data['ip_hash'] = hash('sha256', $request->ip() . config('app.key'));
        Confession::create($data);
        return back()->with('success', 'Your report has been received anonymously. The Director will review it.');
    }

    public function index()
    {
        $items = Confession::latest()->paginate(20);
        $unread = Confession::where('read', false)->count();
        return view('principal.confessions.index', compact('items', 'unread'));
    }

    public function show(Confession $confession)
    {
        $confession->update(['read' => true]);
        return view('principal.confessions.show', compact('confession'));
    }

    public function flag(Confession $confession)
    {
        $confession->update(['flagged' => !$confession->flagged]);
        return back()->with('success', 'Updated.');
    }

    public function destroy(Confession $confession)
    {
        $confession->delete();
        return redirect()->route('principal.confessions.index')->with('success', 'Deleted.');
    }
}
