# Reminder Analytics Documentation

## Overview

The Reminder Analytics system tracks and analyzes reminder delivery performance, providing insights into sent, failed, and upcoming reminders. The system maintains both real-time event tracking and daily aggregated statistics.

## Features

1. **Real-time Event Tracking**
   - Track individual reminder status changes
   - Monitor delivery channels
   - Track failure reasons and retry attempts
   - Store metadata for detailed analysis

2. **Daily Statistics**
   - Total reminders sent, failed, and pending
   - Success rate calculation
   - Average delivery time
   - Channel-specific statistics
   - Common failure reasons

3. **Admin Dashboard Access**
   - Protected routes for analytics access
   - Filterable analytics data
   - Export capabilities

## Database Structure

### Reminder Analytics Table
Tracks individual reminder events:
```sql
CREATE TABLE reminder_analytics (
    id bigint PRIMARY KEY,
    reminder_id bigint FOREIGN KEY,
    status varchar,
    delivery_channel varchar,
    sent_at timestamp,
    failed_at timestamp,
    failure_reason text,
    retry_count integer,
    metadata json,
    created_at timestamp,
    updated_at timestamp
);
```

### Analytics Summaries Table
Stores daily aggregated statistics:
```sql
CREATE TABLE analytics_summaries (
    id bigint PRIMARY KEY,
    date date UNIQUE,
    total_sent integer,
    total_failed integer,
    total_pending integer,
    success_rate decimal(5,2),
    average_delivery_time decimal(10,2),
    channel_stats json,
    failure_reasons json,
    created_at timestamp,
    updated_at timestamp
);
```

## API Endpoints

### Admin Routes (Protected with admin middleware)

1. **Get Analytics Overview**
   ```http
   GET /api/admin/reminders/stats
   ```
   Response:
   ```json
   {
       "total_sent": 1000,
       "total_failed": 50,
       "total_pending": 150,
       "success_rate": 95.24,
       "average_delivery_time": 2.5
   }
   ```

2. **Get Daily Statistics**
   ```http
   GET /api/admin/reminders/stats/daily
   ```
   Query Parameters:
   - `start_date`: Start date for the range (YYYY-MM-DD)
   - `end_date`: End date for the range (YYYY-MM-DD)
   - `channel`: Filter by delivery channel

3. **Get Failure Analysis**
   ```http
   GET /api/admin/reminders/stats/failures
   ```
   Response:
   ```json
   {
       "common_failures": [
           {
               "reason": "Invalid phone number",
               "count": 25
           }
       ],
       "failure_rate_by_channel": {
           "sms": 2.5,
           "email": 1.8
       }
   }
   ```

## Implementation Details

1. **Event Tracking**
   - Use Laravel events to track reminder status changes
   - Automatically update analytics tables
   - Handle retry attempts tracking

2. **Daily Aggregation**
   - Schedule daily summary generation
   - Calculate success rates and averages
   - Store channel-specific statistics

3. **Performance Considerations**
   - Indexed fields for faster queries
   - JSON columns for flexible metadata
   - Daily aggregation to improve query performance

## Usage Examples

### Tracking a Reminder Event

```php
use App\Models\ReminderAnalytics;

ReminderAnalytics::create([
    'reminder_id' => $reminder->id,
    'status' => 'sent',
    'delivery_channel' => 'email',
    'sent_at' => now(),
    'metadata' => [
        'recipient' => $reminder->recipient_email,
        'template' => $reminder->template_name
    ]
]);
```

### Generating Daily Summary

```php
use App\Models\AnalyticsSummary;

AnalyticsSummary::updateOrCreate(
    ['date' => now()->toDateString()],
    [
        'total_sent' => $totalSent,
        'total_failed' => $totalFailed,
        'total_pending' => $totalPending,
        'success_rate' => $successRate,
        'average_delivery_time' => $avgDeliveryTime,
        'channel_stats' => $channelStats,
        'failure_reasons' => $failureReasons
    ]
);
```

## Best Practices

1. **Data Collection**
   - Track all reminder status changes
   - Store relevant metadata
   - Maintain accurate timestamps

2. **Performance**
   - Use indexes for frequently queried fields
   - Aggregate data daily
   - Clean up old detailed records periodically

3. **Security**
   - Restrict access to admin users only
   - Validate and sanitize date ranges
   - Implement rate limiting on API endpoints

## Testing

Run the test suite:
```bash
php artisan test --filter=ReminderAnalyticsTest
``` 