<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReminderAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'reminder_id',
        'status',
        'delivery_channel',
        'sent_at',
        'failed_at',
        'failure_reason',
        'retry_count',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
        'retry_count' => 'integer',
    ];

    /**
     * Get the reminder that owns this analytics record.
     */
    public function reminder(): BelongsTo
    {
        return $this->belongsTo(AppointmentReminder::class, 'reminder_id');
    }

    /**
     * Scope for successful reminders
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed reminders
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
} 