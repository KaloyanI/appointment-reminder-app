<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->integer('minutes_before')->unsigned();
            $table->enum('notification_method', ['email', 'sms', 'both']);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['appointment_id', 'is_enabled']);
            $table->index('minutes_before');
        });

        // Remove the single reminder column from appointments table if it exists
        if (Schema::hasColumn('appointments', 'reminder_before_minutes')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('reminder_before_minutes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the single reminder column in appointments table
        if (!Schema::hasColumn('appointments', 'reminder_before_minutes')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->integer('reminder_before_minutes')->default(60)->after('recurrence_rule');
            });
        }

        Schema::dropIfExists('appointment_reminders');
    }
};
