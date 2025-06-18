<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create([
            'user_id' => $this->user->id,
            'preferred_notification_method' => 'email'
        ]);
    }

    #[Test]
    public function it_can_update_notification_preferences()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/clients/{$this->client->id}/notification-preferences", [
                'preferred_notification_method' => 'sms'
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Notification preferences updated successfully',
                'data' => [
                    'id' => $this->client->id,
                    'preferred_notification_method' => 'sms'
                ]
            ]);

        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
            'preferred_notification_method' => 'sms'
        ]);
    }

    #[Test]
    public function it_validates_notification_method()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/clients/{$this->client->id}/notification-preferences", [
                'preferred_notification_method' => 'invalid'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['preferred_notification_method']);
    }

    #[Test]
    public function it_prevents_unauthorized_users_from_updating_preferences()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->putJson("/api/clients/{$this->client->id}/notification-preferences", [
                'preferred_notification_method' => 'sms'
            ]);

        $response->assertForbidden();
    }
} 