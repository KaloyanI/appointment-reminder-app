<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticsSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_sent',
        'total_failed',
        'total_pending',
        'success_rate',
        'average_delivery_time',
        'channel_stats',
        'failure_reasons',
    ];

    protected $casts = [
        'date' => 'date',
        'total_sent' => 'integer',
        'total_failed' => 'integer',
        'total_pending' => 'integer',
        'success_rate' => 'decimal:2',
        'average_delivery_time' => 'decimal:2',
        'channel_stats' => 'array',
        'failure_reasons' => 'array',
    ];

    /**
     * Scope for filtering by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Generate or update daily summary
     */
    public static function generateDailySummary($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        $analytics = ReminderAnalytics::whereDate('created_at', $date);
        
        $totalSent = $analytics->successful()->count();
        $totalFailed = $analytics->failed()->count();
        $totalPending = $analytics->pending()->count();
        $total = $totalSent + $totalFailed + $totalPending;
        
        $successRate = $total > 0 ? ($totalSent / $total) * 100 : 0;
        
        $channelStats = $analytics
            ->selectRaw('delivery_channel, count(*) as total')
            ->groupBy('delivery_channel')
            ->pluck('total', 'delivery_channel')
            ->toArray();
            
        $failureReasons = $analytics
            ->failed()
            ->selectRaw('failure_reason, count(*) as count')
            ->groupBy('failure_reason')
            ->pluck('count', 'failure_reason')
            ->toArray();
            
        return static::updateOrCreate(
            ['date' => $date],
            [
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed,
                'total_pending' => $totalPending,
                'success_rate' => $successRate,
                'channel_stats' => $channelStats,
                'failure_reasons' => $failureReasons,
            ]
        );
    }
} 