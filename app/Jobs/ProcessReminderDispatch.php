<?php

namespace App\Jobs;

use App\Models\ReminderDispatch;
use App\Notifications\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReminderDispatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ReminderDispatch $reminderDispatch
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if the reminder is still pending and not cancelled
            if ($this->reminderDispatch->status !== 'pending') {
                return;
            }

            // Check if the appointment is still scheduled
            if ($this->reminderDispatch->appointment->status !== 'scheduled') {
                $this->reminderDispatch->update(['status' => 'cancelled']);
                return;
            }

            // Send the notification
            $this->reminderDispatch->appointment->client->notify(
                new AppointmentReminder($this->reminderDispatch->appointment)
            );

            // Update the reminder status
            $this->reminderDispatch->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Log success
            Log::info('Reminder sent successfully', [
                'reminder_id' => $this->reminderDispatch->id,
                'appointment_id' => $this->reminderDispatch->appointment_id,
            ]);
        } catch (\Exception $e) {
            // Update retry count and status
            $this->reminderDispatch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $this->reminderDispatch->retry_count + 1,
            ]);

            // Log error
            Log::error('Failed to send reminder', [
                'reminder_id' => $this->reminderDispatch->id,
                'appointment_id' => $this->reminderDispatch->appointment_id,
                'error' => $e->getMessage(),
            ]);

            // Rethrow if we should retry
            if ($this->reminderDispatch->retry_count < 3) {
                throw $e;
            }
        }
    }
} 