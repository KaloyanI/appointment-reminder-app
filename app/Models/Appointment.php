<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'timezone',
        'status', // scheduled, completed, cancelled, no_show
        'is_recurring',
        'recurrence_rule', // RRULE format for recurring appointments
        'reminder_before_minutes',
        'location',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'client_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_recurring' => 'boolean',
        'reminder_before_minutes' => 'integer',
    ];

    /**
     * Get the user that owns the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client associated with the appointment.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the reminder dispatches for the appointment.
     */
    public function reminderDispatches(): HasMany
    {
        return $this->hasMany(ReminderDispatch::class);
    }

    /**
     * Schedule a reminder for this appointment.
     */
    public function scheduleReminder(bool $immediate = false): ReminderDispatch
    {
        // Calculate when the reminder should be sent
        $scheduledAt = $immediate 
            ? now() 
            : Carbon::parse($this->start_time)->subMinutes($this->reminder_before_minutes);

        // Create the reminder dispatch
        return $this->reminderDispatches()->create([
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
            'notification_method' => $this->client->preferred_notification_method,
        ]);
    }

    /**
     * Reschedule the pending reminders for this appointment.
     */
    public function rescheduleReminder(): void
    {
        // Cancel any pending reminders
        $this->reminderDispatches()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        // Schedule a new reminder
        $this->scheduleReminder();
    }

    /**
     * Get the start time in the appointment's timezone.
     */
    public function getLocalStartTime(): Carbon
    {
        return Carbon::parse($this->start_time)->setTimezone($this->timezone);
    }

    /**
     * Get the end time in the appointment's timezone.
     */
    public function getLocalEndTime(): Carbon
    {
        return Carbon::parse($this->end_time)->setTimezone($this->timezone);
    }
} 