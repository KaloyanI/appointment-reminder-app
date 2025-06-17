<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ReminderDispatch;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or retrieve the demo user
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
                'business_name' => 'Demo Business',
                'timezone' => 'UTC',
                'email_verified_at' => now(),
            ]
        );

        // Create additional users with unique emails
        User::factory(5)
            ->sequence(fn ($sequence) => ['email' => "user{$sequence->index}@example.com"])
            ->create();

        // Create clients for each user
        User::all()->each(function ($user) {
            // Create 5-10 clients per user with unique emails
            $clients = Client::factory()
                ->count(fake()->numberBetween(5, 10))
                ->sequence(fn ($sequence) => [
                    'email' => "client{$sequence->index}_{$user->id}@example.com"
                ])
                ->create(['user_id' => $user->id]);

            // Create appointments for each client
            $clients->each(function ($client) use ($user) {
                // Create 2-5 appointments per client
                Appointment::factory()
                    ->count(fake()->numberBetween(2, 5))
                    ->create([
                        'user_id' => $user->id,
                        'client_id' => $client->id,
                        'timezone' => $client->timezone,
                    ])
                    ->each(function ($appointment) use ($client) {
                        // Create 1-3 reminder dispatches per appointment
                        ReminderDispatch::factory()
                            ->count(fake()->numberBetween(1, 3))
                            ->create([
                                'appointment_id' => $appointment->id,
                                'notification_method' => $client->preferred_notification_method,
                            ]);
                    });

                // Create some recurring appointments (20% chance)
                if (fake()->boolean(20)) {
                    Appointment::factory()
                        ->recurring()
                        ->create([
                            'user_id' => $user->id,
                            'client_id' => $client->id,
                            'timezone' => $client->timezone,
                        ]);
                }
            });
        });
    }
}
