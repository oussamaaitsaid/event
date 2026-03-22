<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Public landing page — shows all open events
     */
    public function index()
    {
        
        $events = Event::with('organizer:id,name')
            ->where('status', 'open')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'id'          => $event->id,
                    'title'       => $event->title,
                    'description' => $event->description,
                    'date'        => Carbon::parse($event->date)->format('Y-m-d H:i'),
                    'location'    => $event->location,
                    'capacity'    => $event->capacity,
                    'status'      => $event->status,
                    'organizer'   => $event->organizer->name ?? 'Unknown',
                    'spots_left'  => $event->capacity - $event->registrations()->count(),
                ];
            });

        return Inertia::render('Landing', [
            'events' => $events,
        ]);
    }

    /**
     * Public detail page for a single event
     */
    public function show(Event $event)
    {
        return Inertia::render('EventDetail', [
            'event' => [
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description,
                'date'        => Carbon::parse($event->date)->format('Y-m-d H:i'),
                'location'    => $event->location,
                'capacity'    => $event->capacity,
                'status'      => $event->status,
                'organizer'   => $event->organizer->name ?? 'Unknown',
                'spots_left'  => $event->capacity - $event->registrations()->count(),
            ],
        ]);
    }
}
