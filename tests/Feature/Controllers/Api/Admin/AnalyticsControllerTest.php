<?php

namespace Tests\Feature\Controllers\Api\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\ReminderAnalytics;
use App\Models\AnalyticsSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    /** @test */
    public function non_admin_users_cannot_access_analytics()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/analytics/overview');

        $response->assertForbidden();
    }

    /** @test */
    public function it_returns_overview_statistics()
    {
        Cache::flush();

        ReminderAnalytics::factory()->count(3)->create(['status' => 'sent']);
        ReminderAnalytics::factory()->create(['status' => 'failed']);
        ReminderAnalytics::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/analytics/overview');

        $response->assertOk()
            ->assertJson([
                'total_sent' => 3,
                'total_failed' => 1,
                'total_pending' => 1,
                'success_rate' => 60.00
            ]);
    }

    /** @test */
    public function it_returns_daily_statistics()
    {
        $startDate = now()->subDays(2)->toDateString();
        $endDate = now()->toDateString();

        AnalyticsSummary::factory()->create([
            'date' => now()->subDay(),
            'total_sent' => 10,
            'total_failed' => 2,
            'channel_stats' => ['email' => 8, 'sms' => 2]
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/analytics/daily?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk()
            ->assertJsonCount(1);
    }

    /** @test */
    public function it_validates_date_range_for_daily_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/analytics/daily');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function it_returns_failure_analysis()
    {
        Cache::flush();

        ReminderAnalytics::factory()->count(2)->create([
            'status' => 'failed',
            'delivery_channel' => 'email',
            'failure_reason' => 'Invalid email'
        ]);

        ReminderAnalytics::factory()->create([
            'status' => 'failed',
            'delivery_channel' => 'sms',
            'failure_reason' => 'Invalid phone'
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/analytics/failures');

        $response->assertOk()
            ->assertJsonStructure([
                'common_failures',
                'failure_rate_by_channel'
            ]);
    }

    /** @test */
    public function it_filters_daily_statistics_by_channel()
    {
        $startDate = now()->subDays(2)->toDateString();
        $endDate = now()->toDateString();

        AnalyticsSummary::factory()->create([
            'date' => now()->subDay(),
            'total_sent' => 10,
            'channel_stats' => ['email' => 8, 'sms' => 2]
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/analytics/daily?start_date={$startDate}&end_date={$endDate}&channel=email");

        $response->assertOk()
            ->assertJsonFragment(['total_sent' => 8]);
    }
} 