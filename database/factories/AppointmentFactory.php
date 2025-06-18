<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::instance(fake()->dateTimeBetween('now', '+2 months'));
        $endTime = (clone $startTime)->addHours(fake()->numberBetween(1, 3));

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional(0.8)->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'timezone' => fake()->randomElement([
                'UTC', 'America/New_York', 'Europe/London', 
                'Asia/Tokyo', 'Australia/Sydney'
            ]),
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
            'is_recurring' => fake()->boolean(20), // 20% chance of being recurring
            'recurrence_rule' => function (array $attributes) {
                return $attributes['is_recurring'] ? 'FREQ=WEEKLY;COUNT=4' : null;
            },
            'location' => fake()->optional(0.9)->address(),
            'notes' => fake()->optional(0.6)->text(200),
        ];
    }

    /**
     * Configure the factory to create a scheduled appointment.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    /**
     * Configure the factory to create a recurring appointment.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=4',
        ]);
    }
} 