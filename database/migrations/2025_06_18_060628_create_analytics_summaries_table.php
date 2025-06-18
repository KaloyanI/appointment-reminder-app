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
        Schema::create('analytics_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('total_sent')->default(0);
            $table->integer('total_failed')->default(0);
            $table->integer('total_pending')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->decimal('average_delivery_time', 10, 2)->nullable();
            $table->json('channel_stats')->nullable();
            $table->json('failure_reasons')->nullable();
            $table->timestamps();

            // Index for date queries
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_summaries');
    }
}; 