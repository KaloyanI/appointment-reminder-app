<?php

namespace Database\Factories;

use App\Models\ReminderAnalytics;
use App\Models\AppointmentReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderAnalyticsFactory extends Factory
{
    protected $model = ReminderAnalytics::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['sent', 'failed', 'pending']);
        $channel = $this->faker->randomElement(['email', 'sms']);
        
        return [
            'reminder_id' => AppointmentReminder::factory(),
            'status' => $status,
            'delivery_channel' => $channel,
            'sent_at' => $status === 'sent' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
            'failure_reason' => $status === 'failed' ? $this->faker->sentence : null,
            'retry_count' => $this->faker->numberBetween(0, 3),
            'metadata' => [
                'recipient' => $channel === 'email' ? $this->faker->email : $this->faker->phoneNumber,
                'template' => 'default_template'
            ],
        ];
    }

    public function sent(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ];
        });
    }

    public function failed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'sent_at' => null,
                'failed_at' => now(),
                'failure_reason' => $this->faker->sentence,
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'sent_at' => null,
                'failed_at' => null,
                'failure_reason' => null,
            ];
        });
    }
} 