<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

       

        // Open events (not registered yet)
        $events = Event::with('organizer:id,name')
            ->where('status', 'open')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($event) use ($user) {
                $isRegistered = $event->registrations()
                    ->where('user_id', $user->id)
                    ->where('status', 'registered')
                    ->exists();

                return [
                    'id'           => $event->id,
                    'title'        => $event->title,
                    'description'  => $event->description,
                    'date'         => \Carbon\Carbon::parse($event->date)->format('M d, Y · H:i'),
                    'location'     => $event->location,
                    'capacity'     => $event->capacity,
                    'status'       => $event->status,
                    'organizer'    => $event->organizer->name ?? 'Unknown',
                    'spots_left'   => $event->capacity - $event->registrations()->count(),
                    'is_registered'=> $isRegistered,
                ];
            });

        // My registrations
        $myRegistrations = Registration::with('event')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'status'     => $r->status,
                'event_id'   => $r->event_id,
                'event_title'=> $r->event->title ?? 'Deleted Event',
                'event_date' => $r->event ? \Carbon\Carbon::parse($r->event->date)->format('M d, Y · H:i') : '—',
                'event_location' => $r->event->location ?? '—',
                'event_status'   => $r->event->status ?? '—',
            ]);

        return Inertia::render('User/Dashboard', [
            'events'          => $events,
            'myRegistrations' => $myRegistrations,
        ]);
    }
}
