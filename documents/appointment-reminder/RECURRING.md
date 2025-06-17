# Recurring Appointments Guide

## Overview
The Appointment Reminder System supports recurring appointments using the iCalendar RRULE format. This allows for complex recurring patterns while maintaining compatibility with calendar systems.

## RRULE Format

The `recurrence_rule` field uses the iCalendar RRULE specification. Here are common patterns:

```
# Weekly on Monday
FREQ=WEEKLY;BYDAY=MO

# Every two weeks on Tuesday
FREQ=WEEKLY;INTERVAL=2;BYDAY=TU

# Monthly on the first Monday
FREQ=MONTHLY;BYDAY=1MO

# Every day
FREQ=DAILY

# Every weekday
FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR

# Monthly on the 15th
FREQ=MONTHLY;BYMONTHDAY=15

# Yearly on March 1st
FREQ=YEARLY;BYMONTH=3;BYMONTHDAY=1
```

## Creating Recurring Appointments

When creating an appointment, set the following fields:

```json
{
    "is_recurring": true,
    "recurrence_rule": "FREQ=WEEKLY;BYDAY=MO",
    // ... other appointment fields
}
```

## Common Patterns

### 1. Weekly Appointments
```json
{
    "is_recurring": true,
    "recurrence_rule": "FREQ=WEEKLY;BYDAY=MO",
    "start_time": "2024-03-25T10:00:00",
    "end_time": "2024-03-25T11:00:00",
    "timezone": "America/New_York"
}
```

### 2. Monthly Appointments
```json
{
    "is_recurring": true,
    "recurrence_rule": "FREQ=MONTHLY;BYDAY=1MO",
    "start_time": "2024-03-25T14:00:00",
    "end_time": "2024-03-25T15:00:00",
    "timezone": "Europe/London"
}
```

### 3. Daily Appointments
```json
{
    "is_recurring": true,
    "recurrence_rule": "FREQ=DAILY",
    "start_time": "2024-03-25T09:00:00",
    "end_time": "2024-03-25T09:30:00",
    "timezone": "Asia/Tokyo"
}
```

## Limiting Recurrences

You can limit recurring appointments using:

### 1. Count
Limit by number of occurrences:
```
FREQ=WEEKLY;COUNT=10;BYDAY=MO
```

### 2. Until Date
Limit by end date:
```
FREQ=WEEKLY;UNTIL=20241231T235959Z;BYDAY=MO
```

### 3. Interval
Skip periods:
```
FREQ=WEEKLY;INTERVAL=2;BYDAY=MO  # Every other Monday
```

## Best Practices

1. **Timezone Handling**:
   - Always specify the timezone
   - Consider daylight saving time changes
   - Use consistent timezone across recurrences

2. **Reminders**:
   - Each occurrence gets its own reminder
   - Reminders are created as appointments are generated
   - Monitor reminder queue for recurring appointments

3. **Performance**:
   - Use reasonable COUNT or UNTIL limits
   - Consider database load for frequent recurrences
   - Monitor system resources

4. **Modification**:
   - Changes affect future occurrences only
   - Consider handling exceptions to the pattern
   - Document changes in appointment notes

## Examples

### 1. Weekly Client Session
```json
{
    "title": "Weekly Therapy Session",
    "is_recurring": true,
    "recurrence_rule": "FREQ=WEEKLY;BYDAY=WE;COUNT=12",
    "start_time": "2024-03-27T15:00:00",
    "end_time": "2024-03-27T16:00:00",
    "timezone": "America/Chicago",
    "reminder_before_minutes": 1440
}
```

### 2. Monthly Review
```json
{
    "title": "Monthly Progress Review",
    "is_recurring": true,
    "recurrence_rule": "FREQ=MONTHLY;BYDAY=1MO;COUNT=6",
    "start_time": "2024-04-01T10:00:00",
    "end_time": "2024-04-01T11:00:00",
    "timezone": "Europe/Paris",
    "reminder_before_minutes": 2880
}
```

### 3. Daily Check-in
```json
{
    "title": "Daily Health Check",
    "is_recurring": true,
    "recurrence_rule": "FREQ=DAILY;BYDAY=MO,TU,WE,TH,FR",
    "start_time": "2024-03-25T09:00:00",
    "end_time": "2024-03-25T09:15:00",
    "timezone": "Asia/Tokyo",
    "reminder_before_minutes": 30
}
```

## Troubleshooting

1. **Invalid RRULE Format**:
   - Verify syntax using an RRULE validator
   - Check for proper capitalization
   - Ensure valid day/month values

2. **Timezone Issues**:
   - Verify timezone string is valid
   - Check for daylight saving time handling
   - Monitor actual occurrence times

3. **Performance Issues**:
   - Review recurrence limits
   - Monitor database size
   - Check reminder queue length

4. **Reminder Failures**:
   - Check logs for errors
   - Verify reminder timing
   - Monitor queue worker status 