<?php

namespace App\Http\Controllers;

use App\Models\LecturerInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LecturerInviteController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email' // Optional email tracking
        ]);

        $invite = LecturerInvite::create([
            'token' => Str::random(32),
            'inviter_id' => auth()->id(),
            'email' => $request->email,
            'expires_at' => Carbon::today()->addDays(7),
        ]);

        $url = URL::temporarySignedRoute(
            'lecturer.invite',
            $invite->expires_at,
            ['invite' => $invite->token]
        );

        return response()->json([
            'url' => $url,
            'expires_at' => $invite->expires_at
        ]);
    }

    public function redeem($token)
    {
        $invite = LecturerInvite::where('token', $token)->firstOrFail();

        if (!$invite->isValid()) {
            abort(403, 'This invitation link is invalid or has expired');
        }

        // Mark as used immediately to prevent reuse
        $invite->update(['used' => true]);

        // Store invite in session for registration validation
        session(['lecturer_invite' => $invite->token]);

        return redirect()->route('teachers.register');
    }

    public function registerForm()
    {
        if (!session()->has('lecturer_invite')) {
            abort(403, 'You need a valid invitation to register as a lecturer');
        }
        return view('teachers.register',[
            'invite_token' => session('lecturer_invite')
        ]);
    }

    public function inviteForm()
    {
        return view('teachers.invite-create');
    }

}
