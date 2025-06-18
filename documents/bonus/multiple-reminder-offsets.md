# Custom Reminder Offsets: Multiple Reminders

## Current Implementation
Currently, the system supports a single reminder per appointment through the `reminder_before_minutes` field in the `appointments` table.

## Proposed Enhancement

### 1. Database Changes

Create a new table `appointment_reminders` to store multiple reminder settings per appointment:

```sql
CREATE TABLE appointment_reminders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    minutes_before INTEGER NOT NULL,
    notification_method ENUM('email', 'sms', 'both') NOT NULL,
    is_enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);
```

### 2. Model Changes

#### Create AppointmentReminder Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReminder extends Model
{
    protected $fillable = [
        'appointment_id',
        'minutes_before',
        'notification_method',
        'is_enabled'
    ];

    protected $casts = [
        'minutes_before' => 'integer',
        'is_enabled' => 'boolean'
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
```

#### Update Appointment Model
```php
class Appointment extends Model
{
    // Add relationship
    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    // Update schedule reminder method
    public function scheduleReminders(): Collection
    {
        return $this->reminders()
            ->where('is_enabled', true)
            ->get()
            ->map(function ($reminder) {
                $scheduledAt = Carbon::parse($this->start_time)
                    ->subMinutes($reminder->minutes_before);

                return $this->reminderDispatches()->create([
                    'scheduled_at' => $scheduledAt,
                    'status' => 'pending',
                    'notification_method' => $reminder->notification_method,
                ]);
            });
    }
}
```

### 3. API Changes

#### Update Appointment Controller
```php
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        // ... existing validation rules ...
        'reminders' => 'array',
        'reminders.*.minutes_before' => 'required|integer|min:1',
        'reminders.*.notification_method' => ['required', Rule::in(['email', 'sms', 'both'])],
    ]);

    $appointment = Auth::user()->appointments()->create([
        // ... existing appointment creation ...
    ]);

    // Create reminders
    if (isset($validated['reminders'])) {
        foreach ($validated['reminders'] as $reminder) {
            $appointment->reminders()->create($reminder);
        }
    }

    // Schedule all reminders
    $appointment->scheduleReminders();

    return response()->json($appointment->load(['client', 'reminders', 'reminderDispatches']), 201);
}
```

### 4. Example Usage

#### Creating an Appointment with Multiple Reminders
```json
POST /api/appointments
{
    "title": "Dental Checkup",
    "client_id": 1,
    "start_time": "2024-03-25T14:00:00",
    "end_time": "2024-03-25T15:00:00",
    "timezone": "America/New_York",
    "reminders": [
        {
            "minutes_before": 1440,  // 24 hours before
            "notification_method": "email"
        },
        {
            "minutes_before": 60,    // 1 hour before
            "notification_method": "sms"
        },
        {
            "minutes_before": 15,    // 15 minutes before
            "notification_method": "both"
        }
    ]
}
```

### 5. Benefits

1. **Flexibility**: Users can set multiple reminders with different timings
2. **Custom Notification Methods**: Each reminder can use a different notification method
3. **Independent Control**: Reminders can be enabled/disabled individually
4. **Scalability**: Easy to add more reminder options without changing appointment structure

### 6. Implementation Steps

1. Create migration for `appointment_reminders` table
2. Create `AppointmentReminder` model
3. Update `Appointment` model with new relationship and methods
4. Update API endpoints to handle multiple reminders
5. Update frontend to support multiple reminder selection
6. Update reminder processing job to handle different notification methods
7. Add validation rules for reminder combinations

### 7. Best Practices

1. **Validation**
   - Ensure reminder times don't overlap
   - Validate maximum number of reminders per appointment
   - Check if notification methods are available for client

2. **Performance**
   - Index the `minutes_before` column for efficient querying
   - Consider bulk reminder creation for better performance
   - Optimize reminder dispatch queries

3. **User Experience**
   - Provide preset reminder combinations
   - Allow saving reminder preferences
   - Show clear timeline of scheduled reminders

4. **Error Handling**
   - Handle failed notifications appropriately
   - Provide clear error messages for invalid combinations
   - Log reminder creation and dispatch events

### 8. Future Enhancements

1. **Custom Messages**
   - Allow custom message per reminder
   - Support templates for different reminder times

2. **Smart Scheduling**
   - Consider business hours
   - Respect client's timezone and preferences
   - Avoid sending too many reminders in short period

3. **Analytics**
   - Track reminder effectiveness
   - Monitor preferred reminder times
   - Analyze notification method success rates 