<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Create user directly — no factory, no email_verified_at
        $user = User::firstOrCreate(
            ['email' => 'organizer@demo.com'],
            [
                'name'     => 'Demo Organizer',
                'password' => Hash::make('password'),
            ]
        );

        $events = [
            [
                'title'       => 'Laravel & Vue.js Workshop',
                'description' => 'A hands-on full-day workshop covering the modern Laravel + Vite + Vue 3 stack. Build real apps from scratch.',
                'date'        => now()->addDays(7),
                'location'    => 'Casablanca Tech Hub, Morocco',
                'capacity'    => 30,
                'status'      => 'open',
            ],
            [
                'title'       => 'UX Design Conference 2026',
                'description' => 'Join industry leaders discussing the future of user experience, accessibility, and design systems.',
                'date'        => now()->addDays(14),
                'location'    => 'Rabat Convention Center',
                'capacity'    => 200,
                'status'      => 'open',
            ],
            [
                'title'       => 'AI & Machine Learning Seminar',
                'description' => 'Explore the latest advances in AI, from large language models to computer vision applications.',
                'date'        => now()->addDays(21),
                'location'    => 'Online (Zoom)',
                'capacity'    => 500,
                'status'      => 'open',
            ],
        ];

        foreach ($events as $data) {
            Event::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['user_id' => $user->id])
            );
        }
    }
}
