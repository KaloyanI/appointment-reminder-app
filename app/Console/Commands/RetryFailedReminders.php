<?php

namespace App\Console\Commands;

use App\Jobs\RetryFailedReminder;
use App\Models\ReminderDispatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:retry-failed 
                          {--hours=24 : Only retry reminders that failed within the last N hours}
                          {--force : Force retry even if max attempts reached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed reminder dispatches';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $hours = $this->option('hours');
        $force = $this->option('force');
        $maxAttempts = config('reminders.retries.max_attempts', 3);
        $batchSize = config('reminders.processing.batch_size', 50);

        $query = ReminderDispatch::query()
            ->where('status', 'failed')
            ->where('updated_at', '>=', now()->subHours($hours))
            ->with(['appointment']);

        // Unless forced, only retry reminders that haven't exceeded max attempts
        if (!$force) {
            $query->where('retry_count', '<', $maxAttempts);
        }

        $totalReminders = $query->count();
        
        if ($totalReminders === 0) {
            $this->info('No failed reminders found to retry.');
            return;
        }

        $this->info("Found {$totalReminders} failed reminders to retry.");

        // Process in batches to avoid memory issues
        $query->chunkById($batchSize, function ($reminders) {
            foreach ($reminders as $reminder) {
                RetryFailedReminder::dispatch($reminder);
                $this->line("Queued reminder {$reminder->id} for retry.");
            }
        });

        if (config('reminders.monitoring.detailed_logging')) {
            Log::info('Retry failed reminders command completed', [
                'total_reminders' => $totalReminders,
                'hours' => $this->option('hours'),
                'force' => $this->option('force'),
            ]);
        }

        $this->info('All failed reminders have been queued for retry.');
    }
} 