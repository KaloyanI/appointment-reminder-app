<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ReminderDispatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReminderController extends Controller
{
    /**
     * Display a listing of reminders.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'status' => ['nullable', Rule::in(['pending', 'sent', 'failed'])],
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = ReminderDispatch::query()
            ->with(['appointment.client'])
            ->whereHas('appointment', function ($query) {
                $query->where('user_id', Auth::id());
            });

        if ($request->appointment_id) {
            $query->where('appointment_id', $request->appointment_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $reminders = $query->orderBy('scheduled_at')->paginate($request->per_page ?? 15);

        return response()->json($reminders);
    }

    /**
     * Manually trigger a reminder for an appointment.
     */
    public function trigger(Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $reminder = $appointment->scheduleReminder(true); // true for immediate dispatch

        return response()->json($reminder, 201);
    }

    /**
     * Retry a failed reminder.
     */
    public function retry(ReminderDispatch $reminder): JsonResponse
    {
        if ($reminder->appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        if ($reminder->status !== 'failed') {
            return response()->json(['message' => 'Only failed reminders can be retried.'], 422);
        }

        $reminder->retry();

        return response()->json($reminder->fresh());
    }
} 