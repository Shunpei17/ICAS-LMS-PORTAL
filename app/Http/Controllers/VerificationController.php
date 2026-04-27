<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        if ($user->is_verified) {
            return redirect()->route('dashboard');
        }

        return view('auth.verification', compact('user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->enrollment_type === 'new_enrollee') {
            $request->validate(['verification_file' => 'required|file|mimes:jpg,png,pdf|max:2048']);
        } else {
            $request->validate(['verification_file' => 'required|file|mimes:jpg,png,pdf|max:2048']);
        }

        $path = $request->file('verification_file')->store('public/verifications');
        
        $user->verification_file = $path;
        $user->status = 'pending';
        $user->save();

        return redirect()->route('login')->with('status', 'Verification submitted. Please wait for admin approval.');
    }
}
