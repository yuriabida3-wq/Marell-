<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::latest();
        if ($request->boolean('unhandled')) $query->where('handled', false);
        $messages = $query->paginate(20)->withQueryString();
        $unhandled = Contact::where('handled', false)->count();
        return view('principal.contacts.index', compact('messages', 'unhandled'));
    }

    public function show(Contact $contact)
    {
        if (!$contact->handled) {
            $contact->update(['handled' => true]);
        }
        return view('principal.contacts.show', compact('contact'));
    }

    public function toggle(Contact $contact)
    {
        $contact->update(['handled' => !$contact->handled]);
        return back()->with('success', 'Updated.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('principal.contacts.index')->with('success', 'Message deleted.');
    }
}
