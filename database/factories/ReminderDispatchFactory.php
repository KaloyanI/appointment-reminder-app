<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ReminderDispatch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReminderDispatch>
 */
class ReminderDispatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReminderDispatch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduledAt = Carbon::instance(fake()->dateTimeBetween('-1 week', '+1 month'));
        $status = fake()->randomElement(['pending', 'sent', 'failed']);
        
        // Only set sent_at if status is 'sent' or 'failed', and ensure it's after scheduled_at
        $sentAt = in_array($status, ['sent', 'failed']) 
            ? $scheduledAt->copy()->addMinutes(fake()->numberBetween(1, 60))
            : null;

        return [
            'appointment_id' => Appointment::factory(),
            'scheduled_at' => $scheduledAt,
            'sent_at' => $sentAt,
            'status' => $status,
            'notification_method' => fake()->randomElement(['email', 'sms', 'both']),
            'error_message' => function (array $attributes) {
                return $attributes['status'] === 'failed' 
                    ? fake()->sentence() 
                    : null;
            },
            'retry_count' => function (array $attributes) {
                return $attributes['status'] === 'failed' 
                    ? fake()->numberBetween(0, 3) 
                    : 0;
            },
        ];
    }

    /**
     * Configure the factory to create a pending reminder.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
            'error_message' => null,
            'retry_count' => 0,
        ]);
    }

    /**
     * Configure the factory to create a failed reminder.
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            $scheduledAt = $attributes['scheduled_at'] instanceof Carbon 
                ? $attributes['scheduled_at'] 
                : new Carbon($attributes['scheduled_at']);
                
            return [
                'status' => 'failed',
                'sent_at' => $scheduledAt->copy()->addMinutes(fake()->numberBetween(1, 60)),
                'error_message' => fake()->sentence(),
                'retry_count' => fake()->numberBetween(1, 3),
            ];
        });
    }

    /**
     * Configure the factory to create a sent reminder.
     */
    public function sent(): static
    {
        return $this->state(function (array $attributes) {
            $scheduledAt = $attributes['scheduled_at'] instanceof Carbon 
                ? $attributes['scheduled_at'] 
                : new Carbon($attributes['scheduled_at']);
                
            return [
                'status' => 'sent',
                'sent_at' => $scheduledAt->copy()->addMinutes(fake()->numberBetween(1, 60)),
                'error_message' => null,
                'retry_count' => 0,
            ];
        });
    }
} 