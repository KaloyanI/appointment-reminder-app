<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Client;
use App\Models\ReminderDispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\DB;

class ReminderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private Appointment $appointment;
    private AppointmentReminder $reminder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'status' => 'scheduled'
        ]);
        $this->reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id
        ]);
    }

    #[Test]
    public function it_lists_reminder_settings()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/reminder-settings?appointment_id=' . $this->appointment->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'appointment_id',
                        'minutes_before',
                        'notification_method',
                        'is_enabled'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_updates_reminder_settings()
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/reminder-settings/' . $this->reminder->id, [
                'minutes_before' => 120,
                'notification_method' => 'sms',
                'is_enabled' => true
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('appointment_reminders', [
            'id' => $this->reminder->id,
            'minutes_before' => 120,
            'notification_method' => 'sms',
            'is_enabled' => true
        ]);
    }

    #[Test]
    public function it_triggers_immediate_reminder()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/appointments/' . $this->appointment->id . '/trigger-reminder');

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'appointment_id',
                'scheduled_at',
                'status',
                'notification_method'
            ]);

        // Get the actual reminder dispatch
        $dispatch = DB::table('reminder_dispatches')
            ->where('appointment_id', $this->appointment->id)
            ->first();

        $this->assertNotNull($dispatch, 'Reminder dispatch should exist');
        $this->assertContains($dispatch->status, ['pending', 'sent'], 'Status should be either pending or sent');
    }

    #[Test]
    public function it_prevents_unauthorized_access()
    {
        $otherUser = User::factory()->create();
        
        // Try to access settings
        $this->actingAs($otherUser)
            ->getJson('/api/reminder-settings?appointment_id=' . $this->appointment->id)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // Try to update settings
        $this->actingAs($otherUser)
            ->putJson('/api/reminder-settings/' . $this->reminder->id, [
                'minutes_before' => 120
            ])
            ->assertForbidden();

        // Try to trigger reminder
        $this->actingAs($otherUser)
            ->postJson('/api/appointments/' . $this->appointment->id . '/trigger-reminder')
            ->assertForbidden();
    }

    #[Test]
    public function it_validates_reminder_settings_update()
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/reminder-settings/' . $this->reminder->id, [
                'minutes_before' => -1,
                'notification_method' => 'invalid'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['minutes_before', 'notification_method']);
    }
} 