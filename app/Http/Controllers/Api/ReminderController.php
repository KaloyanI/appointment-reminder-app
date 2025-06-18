<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
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
     * Display a listing of reminder settings.
     */
    public function settings(Request $request): JsonResponse
    {
        $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AppointmentReminder::query()
            ->with(['appointment.client'])
            ->whereHas('appointment', function ($query) {
                $query->where('user_id', Auth::id());
            });

        if ($request->appointment_id) {
            $query->where('appointment_id', $request->appointment_id);
        }

        $settings = $query->orderBy('minutes_before')->paginate($request->per_page ?? 15);

        return response()->json($settings);
    }

    /**
     * Update a reminder setting.
     */
    public function updateSetting(Request $request, AppointmentReminder $reminder): JsonResponse
    {
        if ($reminder->appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $validated = $request->validate([
            'minutes_before' => 'sometimes|required|integer|min:1',
            'notification_method' => ['sometimes', 'required', Rule::in(['email', 'sms', 'both'])],
            'is_enabled' => 'sometimes|required|boolean',
        ]);

        $reminder->update($validated);

        // If the reminder was modified and is enabled, reschedule it
        if ($reminder->is_enabled && 
            ($reminder->wasChanged('minutes_before') || $reminder->wasChanged('notification_method'))) {
            $reminder->appointment->scheduleReminders();
        }

        return response()->json($reminder->load('appointment'));
    }

    /**
     * Manually trigger a reminder for an appointment.
     */
    public function trigger(Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $reminder = $appointment->scheduleImmediateReminder();

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

        // Reset the reminder status and retry count
        $reminder->update([
            'status' => 'pending',
            'error_message' => null,
            'retry_count' => 0,
        ]);

        // Dispatch the job immediately
        ProcessReminderDispatch::dispatch($reminder);

        return response()->json($reminder->fresh());
    }
} 