<?php

namespace Database\Factories;

use App\Models\AnalyticsSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsSummaryFactory extends Factory
{
    protected $model = AnalyticsSummary::class;

    public function definition(): array
    {
        $totalSent = $this->faker->numberBetween(50, 200);
        $totalFailed = $this->faker->numberBetween(0, 20);
        $totalPending = $this->faker->numberBetween(0, 30);
        $total = $totalSent + $totalFailed + $totalPending;

        return [
            'date' => $this->faker->date(),
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'total_pending' => $totalPending,
            'success_rate' => ($totalSent / $total) * 100,
            'average_delivery_time' => $this->faker->randomFloat(2, 1, 10),
            'channel_stats' => [
                'email' => $this->faker->numberBetween(30, 150),
                'sms' => $this->faker->numberBetween(20, 100)
            ],
            'failure_reasons' => [
                'Invalid email' => $this->faker->numberBetween(1, 10),
                'Invalid phone' => $this->faker->numberBetween(1, 10),
                'Service unavailable' => $this->faker->numberBetween(1, 5)
            ]
        ];
    }
} 