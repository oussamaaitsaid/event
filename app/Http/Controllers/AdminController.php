<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        // ── Stats ──
        $totalUsers         = User::count();
        $totalEvents        = Event::count();
        $totalRegistrations = Registration::count();
        $openEvents         = Event::where('status', 'open')->count();
        $totalOrganizers    = User::where('role', 'organizer')->count();
        $blockedUsers       = User::where('status', 'blocked')->count();

        // ── Recent Users ──
        $recentUsers = User::latest()
            ->take(8)
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'status'     => $u->status,
                'created_at' => $u->created_at->format('M d, Y'),
            ]);

        // ── All Events ──
        $events = Event::with('organizer:id,name')
            ->withCount('registrations')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($e) => [
                'id'                 => $e->id,
                'title'              => $e->title,
                'date'               => \Carbon\Carbon::parse($e->date)->format('M d, Y'),
                'location'           => $e->location,
                'capacity'           => $e->capacity,
                'status'             => $e->status,
                'organizer'          => $e->organizer->name ?? 'Unknown',
                'registrations_count'=> $e->registrations_count,
            ]);

        // ── Registrations by status ──
        $registeredCount  = Registration::where('status', 'registered')->count();
        $cancelledCount   = Registration::where('status', 'cancelled')->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers'         => $totalUsers,
                'totalEvents'        => $totalEvents,
                'totalRegistrations' => $totalRegistrations,
                'openEvents'         => $openEvents,
                'totalOrganizers'    => $totalOrganizers,
                'blockedUsers'       => $blockedUsers,
                'registeredCount'    => $registeredCount,
                'cancelledCount'     => $cancelledCount,
            ],
            'recentUsers' => $recentUsers,
            'events'      => $events,
        ]);
    }

    // ── Block / Unblock User ──
    public function toggleUserStatus(User $user)
    {
        if ($user->role === 'admin') {
            return back()->withErrors(['error' => 'Cannot block an admin.']);
        }

        $user->update([
            'status' => $user->status === 'active' ? 'blocked' : 'active'
        ]);

        return back();
    }

    // ── Delete Event ──
    public function deleteEvent(Event $event)
    {
        $event->delete();
        return back();
    }

    // ── Delete User ──
    public function deleteUser(User $user)
    {
        if ($user->role === 'admin') {
            return back()->withErrors(['error' => 'Cannot delete an admin.']);
        }

        $user->delete();

        return back();
    }

    // ── Change User Role ──
    public function changeRole(User $user)
    {
        if ($user->role === 'admin') {
            return back()->withErrors(['error' => 'Cannot change admin role.']);
        }

        $user->update([
            'role' => $user->role === 'user' ? 'organizer' : 'user'
        ]);

        return back();
    }
}
