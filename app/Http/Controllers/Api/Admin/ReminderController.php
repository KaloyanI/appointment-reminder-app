<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\ReminderDispatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReminderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of all reminders across the system.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'sent', 'failed'])],
            'user_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = ReminderDispatch::query()
            ->with(['appointment.client', 'appointment.user']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->whereHas('appointment', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        if ($request->start_date) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        $reminders = $query->orderBy('scheduled_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($reminders);
    }

    /**
     * Get reminder statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $query = ReminderDispatch::query();

        if ($request->user_id) {
            $query->whereHas('appointment', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        if ($request->start_date) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        // Get total counts by status
        $statusBreakdown = $query->clone()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Get counts by notification method
        $methodBreakdown = AppointmentReminder::query()
            ->select('notification_method', DB::raw('count(*) as total'))
            ->groupBy('notification_method')
            ->pluck('total', 'notification_method')
            ->toArray();

        // Get daily stats for the last 30 days
        $dailyStats = $query->clone()
            ->select(
                DB::raw('DATE(scheduled_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->whereDate('scheduled_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(scheduled_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'total_reminders' => array_sum($statusBreakdown),
            'status_breakdown' => $statusBreakdown,
            'method_breakdown' => $methodBreakdown,
            'daily_stats' => $dailyStats
        ]);
    }

    /**
     * Display a listing of reminder settings across all users.
     */
    public function settings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AppointmentReminder::query()
            ->with(['appointment.client', 'appointment.user']);

        if ($request->user_id) {
            $query->whereHas('appointment', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        $settings = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($settings);
    }
} 