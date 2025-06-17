<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'timezone' => fake()->randomElement([
                'UTC', 'America/New_York', 'Europe/London', 
                'Asia/Tokyo', 'Australia/Sydney'
            ]),
            'preferred_notification_method' => fake()->randomElement(['email', 'sms', 'both']),
            'notes' => fake()->optional(0.7)->text(200),
        ];
    }
} 