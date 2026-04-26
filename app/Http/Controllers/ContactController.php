<?php

namespace App\Http\Controllers;

use App\Mail\OrganizerRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:20|max:1000',
        ]);

        $user = Auth::user();

        try {
           Mail::to(env('ADMIN_EMAIL'))
            ->send(new OrganizerRequestMail(
                userName:    $user->name,
                userEmail:   $user->email,
                userMessage: $request->message, // ✅ renamed
            ));

            return back()->with('success', 'Request sent successfully!');

        } catch (\Exception $e) {
            Log::error('Mail failed: ' . $e->getMessage());

            // ✅ Return the real error so we can see it
            return back()->withErrors([
                'mail' => $e->getMessage()
            ]);
        }
    }
}
