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
        Schema::create('reminder_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_id')->constrained('appointment_reminders')->onDelete('cascade');
            $table->string('status');
            $table->string('delivery_channel')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('status');
            $table->index('delivery_channel');
            $table->index('sent_at');
            $table->index('failed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_analytics');
    }
}; 