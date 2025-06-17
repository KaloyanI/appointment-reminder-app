# Testing Reminder APIs

This guide explains how to test the reminder system APIs using different approaches.

## Prerequisites

1. Set up your local environment:
```bash
# Copy environment file
cp .env.example .env

# Configure mail settings in .env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed test data (if available)
php artisan db:seed
```

2. Start the local mail server (Mailpit):
```bash
# If using Docker
docker-compose up -d mailpit

# Access Mailpit interface
open http://localhost:8025
```

## 1. Manual Testing with Postman

### Setup Authentication

1. Create a test user:
```http
POST /api/register
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

2. Login to get the token:
```http
POST /api/login
Content-Type: application/json

{
    "email": "test@example.com",
    "password": "password123"
}
```

3. Save the returned token for subsequent requests.

### Test Scenarios

#### A. List Reminders

1. Get all reminders:
```http
GET /api/reminders
Authorization: Bearer {your-token}
```

2. Filter pending reminders:
```http
GET /api/reminders?status=pending
Authorization: Bearer {your-token}
```

3. Test pagination:
```http
GET /api/reminders?per_page=5
Authorization: Bearer {your-token}
```

#### B. Trigger Reminder

1. Create a test appointment:
```http
POST /api/appointments
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "client_id": 1,
    "title": "Test Appointment",
    "description": "Testing reminder system",
    "start_time": "2024-03-25T10:00:00",
    "end_time": "2024-03-25T11:00:00",
    "timezone": "UTC",
    "reminder_before_minutes": 30
}
```

2. Trigger reminder manually:
```http
POST /api/appointments/{appointment_id}/trigger-reminder
Authorization: Bearer {your-token}
```

3. Check Mailpit for the received email.

#### C. Retry Failed Reminder

1. Find a failed reminder:
```http
GET /api/reminders?status=failed
Authorization: Bearer {your-token}
```

2. Retry the failed reminder:
```http
POST /api/reminders/{reminder_id}/retry
Authorization: Bearer {your-token}
```

## 2. Automated Testing

### PHPUnit Tests

Create the following test files:

1. `tests/Feature/ReminderApiTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Appointment;
use App\Models\ReminderDispatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Get authentication token
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        
        $this->token = $response->json('token');
    }

    public function test_can_list_reminders()
    {
        // Create test data
        $client = Client::factory()->for($this->user)->create();
        $appointment = Appointment::factory()
            ->for($this->user)
            ->for($client)
            ->create();
        $reminder = ReminderDispatch::factory()
            ->for($appointment)
            ->create(['status' => 'pending']);

        // Test API endpoint
        $response = $this->getJson('/api/reminders', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'appointment_id',
                            'scheduled_at',
                            'status',
                            'appointment' => [
                                'id',
                                'title',
                                'client'
                            ]
                        ]
                    ]
                ]);
    }

    public function test_can_filter_reminders_by_status()
    {
        // Create test data
        $client = Client::factory()->for($this->user)->create();
        $appointment = Appointment::factory()
            ->for($this->user)
            ->for($client)
            ->create();
        
        ReminderDispatch::factory()
            ->for($appointment)
            ->create(['status' => 'pending']);
        
        ReminderDispatch::factory()
            ->for($appointment)
            ->create(['status' => 'sent']);

        // Test filtering
        $response = $this->getJson('/api/reminders?status=pending', [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    public function test_can_trigger_reminder()
    {
        // Create test data
        $client = Client::factory()->for($this->user)->create();
        $appointment = Appointment::factory()
            ->for($this->user)
            ->for($client)
            ->create();

        // Test trigger endpoint
        $response = $this->postJson("/api/appointments/{$appointment->id}/trigger-reminder", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'appointment_id',
                    'scheduled_at',
                    'status'
                ]);
    }

    public function test_can_retry_failed_reminder()
    {
        // Create test data
        $client = Client::factory()->for($this->user)->create();
        $appointment = Appointment::factory()
            ->for($this->user)
            ->for($client)
            ->create();
        $reminder = ReminderDispatch::factory()
            ->for($appointment)
            ->create(['status' => 'failed']);

        // Test retry endpoint
        $response = $this->postJson("/api/reminders/{$reminder->id}/retry", [], [
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'status',
                    'retry_count'
                ]);
    }
}
```

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Feature/ReminderApiTest.php

# Run with coverage report
php artisan test --coverage
```

## 3. Testing Email Delivery

### Using Mailpit

1. Send a test reminder
2. Open Mailpit interface (http://localhost:8025)
3. Verify the email content:
   - Subject format
   - Recipient address
   - Appointment details
   - Formatting and links

### Using Laravel Mail Fake

For automated tests that verify email sending:

```php
use Illuminate\Support\Facades\Mail;
use App\Notifications\AppointmentReminder;

public function test_reminder_sends_email()
{
    Mail::fake();

    // Trigger reminder
    $response = $this->postJson("/api/appointments/{$appointment->id}/trigger-reminder", [], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    // Assert email was sent
    Mail::assertSent(AppointmentReminder::class, function ($mail) use ($appointment) {
        return $mail->appointment->id === $appointment->id;
    });
}
```

## 4. Common Test Cases

1. **Authentication**
   - Test with invalid token
   - Test with expired token
   - Test without token

2. **Authorization**
   - Test accessing another user's reminders
   - Test triggering reminder for another user's appointment
   - Test retrying another user's reminder

3. **Validation**
   - Test invalid status filters
   - Test invalid pagination values
   - Test non-existent appointment/reminder IDs

4. **Edge Cases**
   - Test with maximum pagination limit
   - Test with multiple simultaneous reminders
   - Test retry limits
   - Test timezone handling

## 5. Troubleshooting

1. **Email Not Received**
   - Check Mailpit is running
   - Verify mail configuration
   - Check Laravel logs
   - Ensure queue worker is running

2. **Failed Tests**
   - Check database migrations
   - Verify test data setup
   - Check authentication token
   - Review Laravel logs

3. **Authorization Issues**
   - Verify token expiration
   - Check user permissions
   - Confirm resource ownership 