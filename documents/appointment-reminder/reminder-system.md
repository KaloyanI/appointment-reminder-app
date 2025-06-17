# Appointment Reminder System Documentation

## Overview

The appointment reminder system is designed to automatically send notifications to clients before their scheduled appointments. The system uses Laravel's queued jobs and notifications system to handle reminder dispatches reliably.

## Key Components

1. **ReminderDispatch Model**
   - Tracks the status of each reminder
   - Stores scheduling and delivery information
   - Manages retry attempts for failed reminders

2. **ProcessReminderDispatch Job**
   - Handles the actual sending of reminders
   - Implements retry logic
   - Logs success and failure events

3. **AppointmentReminder Notification**
   - Formats the reminder content
   - Supports email notifications
   - Handles timezone conversions

## How It Works

### 1. Scheduling Reminders

When an appointment is created or updated, a reminder is automatically scheduled based on the `reminder_before_minutes` setting. For example:

```php
// In Appointment model
public function scheduleReminder(): void
{
    // Calculate when the reminder should be sent
    $scheduledAt = $this->start_time->subMinutes($this->reminder_before_minutes);
    
    // Create a reminder dispatch record
    $reminderDispatch = $this->reminderDispatches()->create([
        'scheduled_at' => $scheduledAt,
        'status' => 'pending',
        'notification_method' => $this->client->preferred_notification_method,
    ]);
    
    // Schedule the job
    ProcessReminderDispatch::dispatch($reminderDispatch)
        ->delay($scheduledAt);
}
```

### 2. Processing Reminders

The `ProcessReminderDispatch` job:
- Verifies the reminder is still pending
- Checks if the appointment is still scheduled
- Sends the notification
- Updates the reminder status
- Handles errors and retries

### 3. Notification Delivery

The `AppointmentReminder` notification:
- Formats the reminder email with appointment details
- Converts times to the client's timezone
- Includes a link to view appointment details

## Configuration

1. **Reminder Timing**
   - Set in the appointment's `reminder_before_minutes` field
   - Can be customized per appointment

2. **Notification Methods**
   - Currently supports email
   - Configurable per client via `preferred_notification_method`

3. **Retry Settings**
   - Maximum 3 retry attempts
   - Automatic retry on failure

## Testing and Simulation

The system uses Laravel's local mail service (Mailpit) for development:

1. Configure `.env` for local mail testing:
```
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@example.com"
```

2. View sent emails:
   - Access Mailpit interface at `http://localhost:8025`
   - All sent emails will be captured here

## Logging

The system logs various events:

1. **Successful Reminders**
```php
Log::info('Reminder sent successfully', [
    'reminder_id' => $reminderDispatch->id,
    'appointment_id' => $reminderDispatch->appointment_id,
]);
```

2. **Failed Reminders**
```php
Log::error('Failed to send reminder', [
    'reminder_id' => $reminderDispatch->id,
    'appointment_id' => $reminderDispatch->appointment_id,
    'error' => $error->getMessage(),
]);
```

## Database Schema

The `reminder_dispatches` table structure:
```php
Schema::create('reminder_dispatches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
    $table->dateTime('scheduled_at');
    $table->dateTime('sent_at')->nullable();
    $table->string('status'); // pending, sent, failed, cancelled
    $table->string('notification_method');
    $table->text('error_message')->nullable();
    $table->integer('retry_count')->default(0);
    $table->timestamps();
});
```

## Example Email Template

The reminder email includes:
- Appointment title
- Date and time (in client's timezone)
- Location
- Description
- Link to appointment details

Example:
```
Subject: Reminder: Dental Checkup

Hello John Doe,

This is a reminder for your upcoming appointment:

Title: Dental Checkup
Date: Monday, March 20, 2024
Time: 10:00 AM
Location: 123 Medical St

Regular dental checkup and cleaning

[View Appointment Details]

Thank you for using our service!
``` 