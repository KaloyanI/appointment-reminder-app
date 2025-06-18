# Recurring Appointments: Support weekly or monthly recurring appointments

## Overview
The Appointment Reminder System supports recurring appointments using the iCalendar RRULE format, allowing for flexible scheduling of repeating appointments on daily, weekly, or monthly basis.

## Implementation Details

### 1. Database Structure
The appointments table includes dedicated fields for recurring appointments:
```sql
`is_recurring` boolean DEFAULT false
`recurrence_rule` string NULLABLE
```

### 2. RRULE Format Support
The system uses the iCalendar RRULE specification for defining recurring patterns:

#### Common Patterns
```
# Weekly Appointments
FREQ=WEEKLY;BYDAY=MO                  # Every Monday
FREQ=WEEKLY;BYDAY=TU,TH              # Every Tuesday and Thursday
FREQ=WEEKLY;INTERVAL=2;BYDAY=WE      # Every other Wednesday

# Monthly Appointments
FREQ=MONTHLY;BYDAY=1MO               # First Monday of every month
FREQ=MONTHLY;BYMONTHDAY=15          # 15th of every month
FREQ=MONTHLY;BYDAY=-1FR             # Last Friday of every month

# Daily Appointments
FREQ=DAILY                          # Every day
FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR   # Every weekday
```

### 3. API Usage

#### Creating a Recurring Appointment
```json
POST /api/appointments
{
    "title": "Weekly Therapy Session",
    "is_recurring": true,
    "recurrence_rule": "FREQ=WEEKLY;BYDAY=WE;COUNT=12",
    "start_time": "2024-03-27T15:00:00",
    "end_time": "2024-03-27T16:00:00",
    "timezone": "America/Chicago",
    "reminder_before_minutes": 1440,
    "client_id": 1,
    "description": "Regular weekly session",
    "location": "Online"
}
```

### 4. Limiting Recurrences

#### By Count
Limit the number of occurrences:
```
FREQ=WEEKLY;COUNT=10;BYDAY=MO    # 10 Monday appointments
```

#### By End Date
Set an end date for the recurring pattern:
```
FREQ=WEEKLY;UNTIL=20241231T235959Z;BYDAY=MO    # Until end of 2024
```

#### By Interval
Skip periods between occurrences:
```
FREQ=WEEKLY;INTERVAL=2;BYDAY=MO    # Every other Monday
FREQ=MONTHLY;INTERVAL=3            # Every three months
```

### 5. Best Practices

#### Timezone Handling
- Always specify the timezone for recurring appointments
- System automatically handles daylight saving time transitions
- All times are stored in UTC and converted to local timezone for display

#### Performance Considerations
- Use reasonable COUNT or UNTIL limits
- Monitor database load for frequent recurrences
- System optimizes reminder generation for recurring appointments

#### Modification Rules
- Changes to recurring appointments affect future occurrences only
- Historical appointments remain unchanged
- Support for exception dates and modified occurrences

### 6. Example Use Cases

#### 1. Weekly Client Session
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

#### 2. Monthly Review Meeting
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

#### 3. Daily Health Check
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

### 7. Reminder System Integration

- Each recurring instance generates its own reminder
- Reminders are created dynamically as appointments are generated
- System maintains separate reminder tracking for each occurrence
- Failed reminders are retried independently

### 8. Troubleshooting

#### Common Issues
1. **Invalid RRULE Format**
   - Verify syntax using an RRULE validator
   - Check for proper capitalization
   - Ensure valid day/month values

2. **Timezone Issues**
   - Verify timezone string is valid
   - Check for daylight saving time handling
   - Monitor actual occurrence times

3. **Performance Issues**
   - Review recurrence limits
   - Monitor database size
   - Check reminder queue length

4. **Reminder Failures**
   - Check logs for errors
   - Verify reminder timing
   - Monitor queue worker status 