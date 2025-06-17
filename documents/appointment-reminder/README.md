# Appointment Reminder System Documentation

## Overview
The Appointment Reminder System is a RESTful API built with Laravel that allows businesses to manage appointments and send automated reminders to clients. The system supports timezone-aware scheduling, recurring appointments, and multiple notification methods.

## Features
- Appointment management (CRUD operations)
- Client association with appointments
- Automated reminder scheduling
- Timezone support
- Multiple notification methods (email, SMS)
- Recurring appointments support
- Queue-based reminder processing
- Failure handling and retry mechanism

## System Components

### 1. Appointment Management
The appointment system is handled by `AppointmentController` which provides the following endpoints:

#### API Endpoints

```http
GET /api/appointments
```
List appointments with optional filters:
- `filter`: upcoming, past, all
- `client_id`: Filter by specific client
- `per_page`: Number of items per page (1-100)

```http
POST /api/appointments
```
Create a new appointment with the following fields:
```json
{
    "client_id": "required|exists:clients,id",
    "title": "required|string|max:255",
    "description": "nullable|string",
    "start_time": "required|date",
    "end_time": "required|date|after:start_time",
    "timezone": "required|timezone",
    "is_recurring": "boolean",
    "recurrence_rule": "nullable|required_if:is_recurring,true",
    "reminder_before_minutes": "required|integer|min:1",
    "location": "nullable|string|max:255",
    "notes": "nullable|string"
}
```

```http
GET /api/appointments/{id}
PUT /api/appointments/{id}
DELETE /api/appointments/{id}
```

### 2. Reminder System

#### API Endpoints

```http
GET /api/reminders
```
List reminders with optional filters:
- `appointment_id`: Filter by specific appointment
- `status`: Filter by status (pending, sent, failed)
- `per_page`: Number of items per page (1-100)

```http
POST /api/appointments/{appointment}/trigger-reminder
```
Manually trigger a reminder for an appointment

```http
POST /api/reminders/{reminder}/retry
```
Retry a failed reminder

#### Reminder Processing
The system processes reminders through several components:

1. **Command**: `reminders:process`
   - Runs every minute via scheduler
   - Finds due reminders
   - Dispatches them to the queue

2. **Job**: `ProcessReminderDispatch`
   - Handles the actual sending of reminders
   - Updates reminder status
   - Manages retry attempts
   - Logs success/failure

### 3. Notification System

The `AppointmentReminder` notification:
- Supports email notifications
- Uses Laravel's built-in notification system
- Formats dates in client's timezone
- Includes appointment details and location
- Provides a link to view appointment details

## Setup and Configuration

### 1. Environment Configuration
Add the following to your `.env` file:
```env
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Running the System
Start the required services:

```bash
# Start the queue worker
php artisan queue:work

# Start the scheduler
php artisan schedule:work

# For local testing with Mailpit
sail up mailpit
```

### 3. Database Migrations
Run the migrations to set up the database:
```bash
php artisan migrate
```

## Timezone Handling

The system handles timezones in the following ways:

1. **Storage**: All dates are stored in UTC in the database
2. **Input**: Dates are accepted in the specified timezone
3. **Output**: Dates are converted to the appropriate timezone:
   - User's timezone for the dashboard
   - Client's timezone for notifications
   - Appointment's timezone for specific appointment views

## Error Handling

The reminder system includes robust error handling:

1. **Retry Mechanism**:
   - Failed reminders can be retried up to 3 times
   - Each retry is logged
   - Error messages are stored for debugging

2. **Logging**:
   - Success and failure events are logged
   - Includes appointment and reminder IDs
   - Stores error messages for debugging

## Best Practices

1. **Creating Appointments**:
   - Always specify the timezone
   - Set appropriate reminder times
   - Use RRULE format for recurring appointments

2. **Managing Reminders**:
   - Monitor the reminder queue
   - Check logs for failed reminders
   - Use retry mechanism for temporary failures

3. **Timezone Considerations**:
   - Always use the client's timezone for client-facing times
   - Consider daylight saving time changes
   - Validate timezone strings against PHP's timezone database

## Security

The API is protected by Laravel Sanctum authentication:
- All endpoints require authentication
- Users can only access their own appointments and clients
- Proper authorization checks are implemented

## Monitoring

Monitor the system using:
- Laravel's built-in logging
- Queue monitoring
- Scheduler monitoring
- Mail delivery logs

## Testing

Run the test suite:
```bash
php artisan test
```

For local testing:
1. Use Mailpit to catch emails
2. Monitor the queue worker output
3. Check the Laravel log files 