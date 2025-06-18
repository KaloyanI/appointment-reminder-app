<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppointmentReminder>
 */
class AppointmentReminderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppointmentReminder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'minutes_before' => fake()->randomElement([15, 30, 60, 120, 1440, 2880]), // 15m, 30m, 1h, 2h, 24h, 48h
            'notification_method' => fake()->randomElement(['email', 'sms', 'both']),
            'is_enabled' => fake()->boolean(80), // 80% chance of being enabled
        ];
    }

    /**
     * Configure the factory to create a disabled reminder.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Configure the factory to create an email reminder.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_method' => 'email',
        ]);
    }

    /**
     * Configure the factory to create an SMS reminder.
     */
    public function sms(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_method' => 'sms',
        ]);
    }

    /**
     * Configure the factory to create a reminder with both notification methods.
     */
    public function bothMethods(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_method' => 'both',
        ]);
    }
} 