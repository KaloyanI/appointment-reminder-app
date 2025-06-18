<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ReminderDispatch;
use App\Notifications\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

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
            // Reload the reminder dispatch with its relationships
            $this->reminderDispatch->load(['appointment.client']);

            // Check if the reminder is still pending and not cancelled
            if ($this->reminderDispatch->status !== 'pending') {
                return;
            }

            // Check if the appointment is still scheduled
            if ($this->reminderDispatch->appointment->status !== 'scheduled') {
                $this->reminderDispatch->update(['status' => 'cancelled']);
                return;
            }

            // Get a fresh instance of the client
            $client = Client::find($this->reminderDispatch->appointment->client_id);

            if (!$client) {
                throw new \Exception('Client not found for this appointment');
            }

            // Send the notification using the Notification facade
            Notification::send($client, new AppointmentReminder($this->reminderDispatch->appointment));

            // Update the reminder status
            $this->reminderDispatch->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Log success
            if (config('reminders.monitoring.detailed_logging')) {
                Log::info('Reminder sent successfully', [
                    'reminder_id' => $this->reminderDispatch->id,
                    'appointment_id' => $this->reminderDispatch->appointment_id,
                    'client_id' => $client->id,
                ]);
            }
        } catch (Throwable $e) {
            // Update status and error message
            $this->reminderDispatch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Log error
            Log::error('Failed to send reminder', [
                'reminder_id' => $this->reminderDispatch->id,
                'appointment_id' => $this->reminderDispatch->appointment_id,
                'error' => $e->getMessage(),
                'retry_count' => $this->reminderDispatch->retry_count,
            ]);

            // Dispatch retry job if we haven't exceeded max attempts
            if ($this->reminderDispatch->retry_count < config('reminders.retries.max_attempts', 3)) {
                RetryFailedReminder::dispatch($this->reminderDispatch);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Additional failure handling if needed
        if (config('reminders.monitoring.detailed_logging')) {
            Log::error('Reminder dispatch job failed', [
                'reminder_id' => $this->reminderDispatch->id,
                'appointment_id' => $this->reminderDispatch->appointment_id,
                'error' => $exception->getMessage(),
                'retry_count' => $this->reminderDispatch->retry_count,
            ]);
        }
    }
} 