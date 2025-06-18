# Appointment Status Management

## Overview
The appointment status feature allows tracking the lifecycle of appointments through various states. Each appointment can be in one of the following states:

- `scheduled`: Default state for new appointments
- `completed`: Appointment was successfully conducted
- `cancelled`: Appointment was cancelled by either party
- `no_show`: Client did not show up for the appointment

## API Endpoints

### Update Appointment Status
```http
PUT /api/appointments/{appointment}/status
```

#### Request Body
```json
{
    "status": "completed"
}
```

#### Valid Status Values
- `scheduled`
- `completed`
- `cancelled`
- `no_show`

#### Response
```json
{
    "id": 1,
    "user_id": 1,
    "client_id": 1,
    "title": "Appointment Title",
    "description": "Appointment Description",
    "start_time": "2024-03-20T10:00:00Z",
    "end_time": "2024-03-20T11:00:00Z",
    "timezone": "UTC",
    "status": "completed",
    "created_at": "2024-03-19T10:00:00Z",
    "updated_at": "2024-03-19T10:00:00Z",
    "client": {
        // Client details...
    },
    "reminder_dispatches": [
        // Reminder details...
    ]
}
```

## Business Rules

1. Only the appointment owner (user) can update the status
2. Status changes from `scheduled` to any other status will automatically cancel any pending reminders
3. Invalid status values will be rejected with a 422 validation error
4. Unauthorized attempts to update status will receive a 403 forbidden error

## Implementation Details

### Database Schema
The status is stored in the `appointments` table:
```sql
status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled'
```

### Reminder Handling
When an appointment status changes from `scheduled` to any other status:
- All pending reminders are automatically cancelled
- No new reminders will be scheduled
- Existing sent reminders remain unchanged for record-keeping

## Usage Examples

### Mark as Completed
```php
$response = $client->put("/api/appointments/{$appointmentId}/status", [
    'status' => 'completed'
]);
```

### Cancel Appointment
```php
$response = $client->put("/api/appointments/{$appointmentId}/status", [
    'status' => 'cancelled'
]);
```

### Mark as No-Show
```php
$response = $client->put("/api/appointments/{$appointmentId}/status", [
    'status' => 'no_show'
]);
``` 