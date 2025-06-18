# Client Reminder Preferences

## Overview
This feature allows clients to set their preferred notification method (email/SMS/both) for appointment reminders. The preference is stored at the client level and will be used as the default notification method for all their appointment reminders.

## Important Notes

### Notification Methods
- `email`: Client will receive only email notifications
- `sms`: Client will receive only SMS notifications
- `both`: Client will receive both email and SMS notifications

### Default Behavior
- Default notification method is set to `email` when creating a new client
- The client's preferred method will be used as a fallback when no specific notification method is set for an appointment reminder
- If a specific notification method is set for an appointment reminder, it will override the client's preference

### Security Considerations
- Only authenticated users can update notification preferences
- Users can only update preferences for their own clients
- API endpoints are protected with Laravel Sanctum authentication

## API Documentation

### Endpoint
```
PUT /api/clients/{client}/notification-preferences
```

### Authentication
- Requires valid Bearer token
- Token must be obtained through login

### Request Body
```json
{
    "preferred_notification_method": "email|sms|both"
}
```

### Response Format
```json
{
    "message": "Notification preferences updated successfully",
    "data": {
        "id": 1,
        "preferred_notification_method": "email",
        // ... other client fields
    }
}
```

### Error Responses
- 401 Unauthorized: Invalid or missing authentication token
- 403 Forbidden: Attempting to update preferences for another user's client
- 422 Unprocessable Entity: Invalid notification method

### Example Usage (JavaScript/Axios)
```javascript
try {
    const response = await axios.put(`/api/clients/${clientId}/notification-preferences`, {
        preferred_notification_method: 'both'
    });
    // Success handling
} catch (error) {
    console.error('Failed to update preferences:', error.response?.data);
}
```

## Implementation Details

### Database Schema
The `clients` table includes:
```php
$table->string('preferred_notification_method')
    ->default('email')
    ->comment('Preferred notification method: email, sms, or both');
```

### Model Configuration
The Client model includes:
```php
protected $fillable = [
    // ... other fields ...
    'preferred_notification_method',
];
```

### Validation Rules
```php
'preferred_notification_method' => 'required|in:email,sms,both'
```

### Authorization
```php
$this->authorize('update', $client);
```

## Integration with Reminder System

### How It Works
1. When a reminder needs to be sent:
   - First checks if the reminder has a specific notification method set
   - If not, uses the client's preferred_notification_method
   - Sends notification(s) based on the determined method

### Example Flow
```php
public function sendReminder(AppointmentReminder $reminder)
{
    $client = $reminder->appointment->client;
    
    // Use reminder-specific method if set, otherwise use client preference
    $method = $reminder->notification_method ?? $client->preferred_notification_method;
    
    switch ($method) {
        case 'email':
            $this->sendEmailReminder($reminder);
            break;
        case 'sms':
            $this->sendSMSReminder($reminder);
            break;
        case 'both':
            $this->sendEmailReminder($reminder);
            $this->sendSMSReminder($reminder);
            break;
    }
}
```

## Testing

### Key Test Cases
1. Successful preference updates
2. Validation of notification methods
3. Authorization checks
4. Integration with reminder system

### Running Tests
```bash
php artisan test --filter=ClientControllerTest
```

## Best Practices

1. **Error Handling**
   - Always validate input
   - Return appropriate HTTP status codes
   - Provide meaningful error messages

2. **Security**
   - Validate user authorization
   - Sanitize input
   - Use proper authentication middleware

3. **Performance**
   - Efficient database queries
   - Proper indexing on frequently queried fields
   - Caching when appropriate

4. **Maintenance**
   - Keep documentation updated
   - Follow Laravel conventions
   - Write comprehensive tests

## Future Enhancements

1. **Scheduling Preferences**
   - Allow clients to set preferred notification times
   - Set quiet hours for notifications

2. **Multiple Contacts**
   - Support multiple email addresses
   - Support multiple phone numbers

3. **Custom Messages**
   - Allow customization of notification messages
   - Support multiple languages

4. **Analytics**
   - Track notification success rates
   - Monitor preferred methods statistics

```php
// Example usage in a service
public function createAppointmentReminder(Appointment $appointment)
{
    $client = $appointment->client;
    
    $reminder = AppointmentReminder::create([
        'appointment_id' => $appointment->id,
        'notification_method' => $client->preferred_notification_method,
        'minutes_before' => 60,
        'is_enabled' => $client->notifications_enabled
    ]);

    return $reminder;
}
``` 