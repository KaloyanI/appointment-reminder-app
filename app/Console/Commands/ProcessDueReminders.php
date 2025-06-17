<?php

namespace App\Console\Commands;

use App\Jobs\ProcessReminderDispatch;
use App\Models\ReminderDispatch;
use Illuminate\Console\Command;

class ProcessDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all due reminder dispatches';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dueReminders = ReminderDispatch::query()
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with(['appointment.client'])
            ->get();

        $count = $dueReminders->count();
        $this->info("Found {$count} due reminders to process.");

        $dueReminders->each(function (ReminderDispatch $reminder) {
            ProcessReminderDispatch::dispatch($reminder);
            $this->line("Dispatched reminder {$reminder->id} for processing.");
        });

        $this->info('All due reminders have been queued for processing.');
    }
} 