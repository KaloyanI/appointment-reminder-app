<?php

namespace App\Jobs;

use App\Models\ReminderDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedReminder implements ShouldQueue
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
        // Check if we should still retry
        if ($this->shouldAbortRetry()) {
            return;
        }

        // Calculate delay for next retry
        $delay = $this->calculateRetryDelay();

        // Reset the reminder status and update retry count
        $this->reminderDispatch->update([
            'status' => 'pending',
            'error_message' => null,
            'retry_count' => $this->reminderDispatch->retry_count + 1,
        ]);

        // Log the retry attempt
        if (config('reminders.monitoring.detailed_logging')) {
            Log::info('Retrying failed reminder', [
                'reminder_id' => $this->reminderDispatch->id,
                'appointment_id' => $this->reminderDispatch->appointment_id,
                'attempt' => $this->reminderDispatch->retry_count,
                'delay' => $delay,
            ]);
        }

        // Dispatch the reminder processing job with calculated delay
        ProcessReminderDispatch::dispatch($this->reminderDispatch)
            ->delay(now()->addMinutes($delay));
    }

    /**
     * Calculate the delay for the next retry using exponential backoff.
     */
    protected function calculateRetryDelay(): int
    {
        $baseDelay = config('reminders.retries.delay_minutes', 5);
        $maxDelay = config('reminders.retries.max_delay_minutes', 60);
        $useExponentialBackoff = config('reminders.retries.use_exponential_backoff', true);

        if (!$useExponentialBackoff) {
            return min($baseDelay, $maxDelay);
        }

        // Calculate exponential backoff: baseDelay * (2 ^ attempt)
        $delay = $baseDelay * (2 ** ($this->reminderDispatch->retry_count));

        // Cap the delay at the maximum configured value
        return min($delay, $maxDelay);
    }

    /**
     * Determine if we should abort the retry attempt.
     */
    protected function shouldAbortRetry(): bool
    {
        $maxAttempts = config('reminders.retries.max_attempts', 3);

        // Check if we've exceeded max attempts
        if ($this->reminderDispatch->retry_count >= $maxAttempts) {
            if (config('reminders.monitoring.detailed_logging')) {
                Log::warning('Maximum retry attempts reached for reminder', [
                    'reminder_id' => $this->reminderDispatch->id,
                    'appointment_id' => $this->reminderDispatch->appointment_id,
                    'max_attempts' => $maxAttempts,
                ]);
            }
            return true;
        }

        // Check if the appointment is still valid
        if (!$this->reminderDispatch->appointment || $this->reminderDispatch->appointment->status !== 'scheduled') {
            if (config('reminders.monitoring.detailed_logging')) {
                Log::info('Aborting reminder retry - appointment no longer valid', [
                    'reminder_id' => $this->reminderDispatch->id,
                    'appointment_id' => $this->reminderDispatch->appointment_id,
                ]);
            }
            return true;
        }

        return false;
    }
} 