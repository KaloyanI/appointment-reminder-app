<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderDispatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'scheduled_at',
        'sent_at',
        'status', // pending, sent, failed
        'notification_method', // email, sms, both
        'error_message',
        'retry_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'appointment_id' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Get the appointment associated with the reminder dispatch.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
} 