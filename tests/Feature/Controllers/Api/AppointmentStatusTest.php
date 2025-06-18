<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ReminderDispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;
    private Appointment $appointment;

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
    }

    #[Test]
    public function it_updates_appointment_status()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/appointments/{$this->appointment->id}/status", [
                'status' => 'completed'
            ]);

        $response->assertOk();
        $this->assertEquals('completed', $this->appointment->fresh()->status);
    }

    #[Test]
    public function it_validates_status_value()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/appointments/{$this->appointment->id}/status", [
                'status' => 'invalid-status'
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_prevents_unauthorized_status_updates()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->putJson("/api/appointments/{$this->appointment->id}/status", [
                'status' => 'completed'
            ]);

        $response->assertForbidden();
        $this->assertEquals('scheduled', $this->appointment->fresh()->status);
    }

    #[Test]
    public function it_cancels_pending_reminders_when_appointment_is_cancelled()
    {
        // Create a pending reminder
        $reminderDispatch = ReminderDispatch::factory()->create([
            'appointment_id' => $this->appointment->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/appointments/{$this->appointment->id}/status", [
                'status' => 'cancelled'
            ]);

        $response->assertOk();
        $this->assertEquals('cancelled', $reminderDispatch->fresh()->status);
    }

    #[Test]
    public function it_requires_status_field()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/appointments/{$this->appointment->id}/status", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
} 