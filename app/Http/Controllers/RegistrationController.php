<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    // ── Register for an event (status = pending) ──
    public function store(Event $event)
    {
        $user = Auth::user();

        // Already pending or registered
        if (Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'registered'])
            ->exists()) {
            return back()->withErrors(['error' => 'You already have a registration for this event.']);
        }

        // Event full
        if ($event->registrations()->where('status', 'registered')->count() >= $event->capacity) {
            return back()->withErrors(['error' => 'Event is full.']);
        }

        // Event not open
        if ($event->status !== 'open') {
            return back()->withErrors(['error' => 'Event is not open.']);
        }

        // ✅ pending instead of registered
        Registration::create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
            'status'   => 'pending',
        ]);

        return back();
    }

    // ── Cancel registration ──
    public function cancel(Registration $registration)
    {
        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        $registration->update(['status' => 'cancelled']);

        return back();
    }

    // ── Approve (organizer only) ──
    public function approve(Registration $registration)
    {
        if ($registration->event->user_id !== Auth::id()) {
            abort(403);
        }

        if ($registration->event->registrations()->where('status', 'registered')->count() >= $registration->event->capacity) {
            return back()->withErrors(['error' => 'Event is full.']);
        }

        $registration->update(['status' => 'registered']);

        return back();
    }

    // ── Reject (organizer only) ──
    public function reject(Registration $registration)
    {
        if ($registration->event->user_id !== Auth::id()) {
            abort(403);
        }

        $registration->update(['status' => 'cancelled']);

        return back();
    }
}
