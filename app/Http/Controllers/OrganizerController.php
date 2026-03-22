<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrganizerController extends Controller
{
    // ── Show Create Form ──
    public function create()
    {
        return Inertia::render('Organizer/CreateEvent');
    }

    // ── Store New Event ──
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'date'        => 'required|date|after:now',
            'location'    => 'required|string|max:255',
            'capacity'    => 'required|integer|min:1|max:10000',
        ]);

        Event::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'date'        => Carbon::parse($data['date']),
            'location'    => $data['location'],
            'capacity'    => $data['capacity'],
            'status'      => 'open',
            'user_id'     => Auth::id(),
        ]);

        return redirect()->route('organizer.events')->with('success', 'Event created successfully!');
    }

    // ── List Organizer Events ──
    public function index()
    {
        $events = Event::where('user_id', Auth::id())
            ->withCount('registrations')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($e) => [
                'id'                  => $e->id,
                'title'               => $e->title,
                'description'         => $e->description,
                'date'                => Carbon::parse($e->date)->format('M d, Y · H:i'),
                'location'            => $e->location,
                'capacity'            => $e->capacity,
                'status'              => $e->status,
                'registrations_count' => $e->registrations_count,
                'spots_left'          => $e->capacity - $e->registrations_count,
            ]);

        return Inertia::render('Organizer/Events', [
            'events'  => $events,
            'success' => session('success'),
        ]);
    }

    // ── Show Edit Form ──
    public function edit(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Organizer/EditEvent', [
            'event' => [
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description,
                'date'        => Carbon::parse($event->date)->format('Y-m-d\TH:i'),
                'location'    => $event->location,
                'capacity'    => $event->capacity,
                'status'      => $event->status,
            ],
        ]);
    }

    // ── Update Event ──
    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'date'        => 'required|date',
            'location'    => 'required|string|max:255',
            'capacity'    => 'required|integer|min:1|max:10000',
            'status'      => 'required|in:open,closed,finished',
        ]);

        $event->update([
            'title'       => $data['title'],
            'description' => $data['description'],
            'date'        => Carbon::parse($data['date']),
            'location'    => $data['location'],
            'capacity'    => $data['capacity'],
            'status'      => $data['status'],
        ]);

        return redirect()->route('organizer.events')->with('success', 'Event updated successfully!');
    }

    // ── Delete Event ──
    public function destroy(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $event->delete();

        return redirect()->route('organizer.events')->with('success', 'Event deleted successfully!');
    }


    // ── View Participants ──
    public function participants(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $participants = $event->registrations()
            ->with('user:id,name,email')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'name'        => $r->user->name ?? 'Deleted User',
                'email'       => $r->user->email ?? '—',
                'status'      => $r->status,
                'registered_at' => $r->created_at->format('M d, Y'),
            ]);

        return Inertia::render('Organizer/Participants', [
            'event' => [
                'id'       => $event->id,
                'title'    => $event->title,
                'date'     => Carbon::parse($event->date)->format('M d, Y · H:i'),
                'location' => $event->location,
                'capacity' => $event->capacity,
                'status'   => $event->status,
            ],
            'participants' => $participants,
        ]);
    }
}
