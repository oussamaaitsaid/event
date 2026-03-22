<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    // Register for an event
    public function store(Event $event)
    {
        $user = Auth::user();

        // Already registered
        if (Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', 'registered')
            ->exists()) {
            return back()->withErrors(['error' => 'Already registered.']);
        }

        // Event full
        if ($event->registrations()->where('status','registered')->count() >= $event->capacity) {
            return back()->withErrors(['error' => 'Event is full.']);
        }

        // Event not open
        if ($event->status !== 'open') {
            return back()->withErrors(['error' => 'Event is not open.']);
        }

        Registration::create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
            'status'   => 'registered',
        ]);

        return back();
    }

    // Cancel registration
    public function cancel(Registration $registration)
    {
        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        $registration->update(['status' => 'cancelled']);

        return back();
    }
}
