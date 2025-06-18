<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\ProcessReminderDispatch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
     * Get the reminder settings for the appointment.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    /**
     * Get the reminder dispatches for the appointment.
     */
    public function reminderDispatches(): HasMany
    {
        return $this->hasMany(ReminderDispatch::class);
    }

    /**
     * Schedule reminders for this appointment.
     */
    public function scheduleReminders(): Collection
    {
        // Cancel any pending reminders
        $this->reminderDispatches()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        // Schedule new reminders for each enabled reminder setting
        return $this->reminders()
            ->where('is_enabled', true)
            ->get()
            ->map(function ($reminder) {
                $scheduledAt = Carbon::parse($this->start_time)
                    ->subMinutes($reminder->minutes_before);

                $reminderDispatch = $this->reminderDispatches()->create([
                    'scheduled_at' => $scheduledAt,
                    'status' => 'pending',
                    'notification_method' => $reminder->notification_method,
                ]);

                ProcessReminderDispatch::dispatch($reminderDispatch)
                    ->delay($scheduledAt);

                return $reminderDispatch;
            });
    }

    /**
     * Schedule an immediate reminder for this appointment.
     */
    public function scheduleImmediateReminder(string $notificationMethod = null): ReminderDispatch
    {
        $reminderDispatch = $this->reminderDispatches()->create([
            'scheduled_at' => now(),
            'status' => 'pending',
            'notification_method' => $notificationMethod ?? $this->client->preferred_notification_method,
        ]);

        ProcessReminderDispatch::dispatch($reminderDispatch);

        return $reminderDispatch;
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