<?php
namespace App\Http\Controllers;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function submit(Request $request) {
        $data = $request->validate([
            'name'=>'required|string|max:120','email'=>'nullable|email|max:120','phone'=>'nullable|string|max:20','subject'=>'nullable|string|max:180','message'=>'required|string|max:3000',
        ]);
        Contact::create($data);
        return redirect()->route('contact')->with('success','Thank you! We have received your message and will respond shortly.');
    }
}
