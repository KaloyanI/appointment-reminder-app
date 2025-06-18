<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReminderAnalytics;
use App\Models\AnalyticsSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get overview statistics
     */
    public function overview()
    {
        $stats = Cache::remember('analytics_overview', 3600, function () {
            $total = ReminderAnalytics::count();
            $totalSent = ReminderAnalytics::successful()->count();
            $totalFailed = ReminderAnalytics::failed()->count();
            $totalPending = ReminderAnalytics::pending()->count();
            
            // Calculate average delivery time in minutes using SQLite-compatible syntax
            $avgDeliveryTime = ReminderAnalytics::whereNotNull('sent_at')
                ->selectRaw('AVG(CAST((julianday(sent_at) - julianday(created_at)) * 24 * 60 AS INTEGER)) as avg_time')
                ->value('avg_time');
            
            return [
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed,
                'total_pending' => $totalPending,
                'success_rate' => $total > 0 ? round(($totalSent / $total) * 100, 2) : 0,
                'average_delivery_time' => $avgDeliveryTime
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get daily statistics
     */
    public function daily(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'channel' => 'nullable|string'
        ]);

        $summaries = AnalyticsSummary::inDateRange(
            $request->start_date,
            $request->end_date
        )->get();

        if ($request->channel) {
            $summaries = $summaries->map(function ($summary) use ($request) {
                $channelStats = $summary->channel_stats[$request->channel] ?? 0;
                $summary->total_sent = $channelStats;
                return $summary;
            });
        }

        return response()->json($summaries);
    }

    /**
     * Get failure analysis
     */
    public function failures()
    {
        $failureStats = Cache::remember('failure_stats', 3600, function () {
            $commonFailures = ReminderAnalytics::failed()
                ->selectRaw('failure_reason, count(*) as count')
                ->groupBy('failure_reason')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            $failureRateByChannel = ReminderAnalytics::selectRaw('
                delivery_channel,
                COUNT(CASE WHEN status = "failed" THEN 1 END) * 100.0 / COUNT(*) as failure_rate
            ')
                ->groupBy('delivery_channel')
                ->get()
                ->pluck('failure_rate', 'delivery_channel');

            return [
                'common_failures' => $commonFailures,
                'failure_rate_by_channel' => $failureRateByChannel
            ];
        });

        return response()->json($failureStats);
    }
} 