# Reminder Retry Logic Documentation

## Overview

The reminder retry system provides a robust mechanism for handling failed reminder attempts. It implements exponential backoff, configurable retry limits, and detailed monitoring capabilities.

## Features

1. **Configurable Retry Settings**
   - Maximum retry attempts
   - Configurable delay between retries
   - Optional exponential backoff
   - Maximum delay cap

2. **Automatic Retry Processing**
   - Failed reminders are automatically queued for retry
   - Exponential backoff reduces system load
   - Intelligent retry abort conditions

3. **Manual Retry Options**
   - Command-line interface for bulk retries
   - API endpoint for individual retries
   - Force retry option for exceeded attempts

4. **Monitoring and Logging**
   - Detailed logging of retry attempts
   - Failure tracking
   - Statistics collection

## Configuration

### Environment Variables

Add these to your `.env` file:
```env
REMINDER_MAX_RETRIES=3
REMINDER_RETRY_DELAY=5
REMINDER_MAX_RETRY_DELAY=60
REMINDER_USE_EXPONENTIAL_BACKOFF=true
REMINDER_BATCH_SIZE=50
REMINDER_TIMEOUT_MINUTES=15
REMINDER_DETAILED_LOGGING=true
REMINDER_TRACK_STATISTICS=true
```

### Configuration File

The `config/reminders.php` file contains all retry-related settings:
```php
'retries' => [
    'max_attempts' => env('REMINDER_MAX_RETRIES', 3),
    'delay_minutes' => env('REMINDER_RETRY_DELAY', 5),
    'max_delay_minutes' => env('REMINDER_MAX_RETRY_DELAY', 60),
    'use_exponential_backoff' => env('REMINDER_USE_EXPONENTIAL_BACKOFF', true),
],
```

## Usage

### Automatic Retries

Failed reminders are automatically queued for retry with exponential backoff. No manual intervention is required.

### Manual Retry Command

Retry failed reminders using the command line:

```bash
# Retry all failed reminders from the last 24 hours
php artisan reminders:retry-failed

# Retry failed reminders from the last 48 hours
php artisan reminders:retry-failed --hours=48

# Force retry even if max attempts reached
php artisan reminders:retry-failed --force
```

### API Endpoint

Retry a specific failed reminder:

```http
POST /api/reminders/{reminder}/retry
Authorization: Bearer {your-token}
```

Response:
```json
{
    "id": 1,
    "status": "pending",
    "retry_count": 1,
    "scheduled_at": "2024-03-20T10:00:00Z"
}
```

## Monitoring

### Logging

The system logs various events when `REMINDER_DETAILED_LOGGING` is enabled:

1. Retry Attempts:
```php
Log::info('Retrying failed reminder', [
    'reminder_id' => $id,
    'attempt' => $count,
    'delay' => $minutes
]);
```

2. Maximum Attempts Reached:
```php
Log::warning('Maximum retry attempts reached for reminder', [
    'reminder_id' => $id,
    'max_attempts' => $max
]);
```

### Statistics

When `REMINDER_TRACK_STATISTICS` is enabled, you can monitor:
- Total retry attempts
- Success/failure rates
- Average attempts before success
- Common failure reasons

## Best Practices

1. **Configuration**
   - Start with default retry settings
   - Adjust based on monitoring data
   - Consider your notification volume

2. **Monitoring**
   - Enable detailed logging in production
   - Monitor retry patterns
   - Set up alerts for high failure rates

3. **Maintenance**
   - Regularly review failed reminders
   - Clean up old failed reminders
   - Monitor queue performance

## Troubleshooting

Common issues and solutions:

1. **High Failure Rates**
   - Check notification service status
   - Verify client contact information
   - Review error messages

2. **Long Retry Queues**
   - Increase queue workers
   - Adjust batch size
   - Review retry delays

3. **Resource Usage**
   - Monitor queue performance
   - Adjust batch sizes
   - Consider queue worker configuration 