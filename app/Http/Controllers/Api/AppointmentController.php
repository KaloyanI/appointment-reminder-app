<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'filter' => ['nullable', Rule::in(['upcoming', 'past', 'all'])],
            'client_id' => 'nullable|exists:clients,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Auth::user()->appointments()
            ->with(['client', 'reminderDispatches'])
            ->orderBy('start_time');

        // Apply filters
        if ($request->filter === 'upcoming') {
            $query->where('start_time', '>', now());
        } elseif ($request->filter === 'past') {
            $query->where('start_time', '<', now());
        }

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        $appointments = $query->paginate($request->per_page ?? 15);

        return response()->json($appointments);
    }

    /**
     * Store a new appointment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'timezone' => 'required|timezone',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|required_if:is_recurring,true|string',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reminders' => 'array',
            'reminders.*.minutes_before' => 'required|integer|min:1',
            'reminders.*.notification_method' => ['required', Rule::in(['email', 'sms', 'both'])],
        ]);

        // Ensure the client belongs to the authenticated user
        $client = Client::findOrFail($validated['client_id']);
        if ($client->user_id !== Auth::id()) {
            return response()->json(['message' => 'This client does not belong to you.'], 403);
        }

        // Convert times to UTC for storage
        $startTime = Carbon::parse($validated['start_time'], $validated['timezone'])->utc();
        $endTime = Carbon::parse($validated['end_time'], $validated['timezone'])->utc();

        $appointment = Auth::user()->appointments()->create([
            ...$validated,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled',
        ]);

        // Create reminders if provided
        if (isset($validated['reminders'])) {
            foreach ($validated['reminders'] as $reminder) {
                $appointment->reminders()->create($reminder);
            }
        } else {
            // Create default reminder (24 hours before)
            $appointment->reminders()->create([
                'minutes_before' => 1440,
                'notification_method' => $client->preferred_notification_method,
            ]);
        }

        // Schedule all reminders
        $appointment->scheduleReminders();

        return response()->json(
            $appointment->load(['client', 'reminders', 'reminderDispatches']), 
            201
        );
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        return response()->json($appointment->load(['client', 'reminderDispatches']));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:clients,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'timezone' => 'sometimes|required|timezone',
            'status' => ['sometimes', 'required', Rule::in(['scheduled', 'completed', 'cancelled', 'no_show'])],
            'is_recurring' => 'sometimes|required|boolean',
            'recurrence_rule' => 'nullable|required_if:is_recurring,true|string',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reminders' => 'sometimes|required|array',
            'reminders.*.id' => 'sometimes|exists:appointment_reminders,id',
            'reminders.*.minutes_before' => 'required|integer|min:1',
            'reminders.*.notification_method' => ['required', Rule::in(['email', 'sms', 'both'])],
            'reminders.*.is_enabled' => 'sometimes|boolean',
        ]);

        // If client_id is being updated, ensure it belongs to the authenticated user
        if (isset($validated['client_id'])) {
            $client = Client::findOrFail($validated['client_id']);
            if ($client->user_id !== Auth::id()) {
                return response()->json(['message' => 'This client does not belong to you.'], 403);
            }
        }

        // Convert times to UTC if provided
        if (isset($validated['start_time'])) {
            $timezone = $validated['timezone'] ?? $appointment->timezone;
            $validated['start_time'] = Carbon::parse($validated['start_time'], $timezone)->utc();
        }
        if (isset($validated['end_time'])) {
            $timezone = $validated['timezone'] ?? $appointment->timezone;
            $validated['end_time'] = Carbon::parse($validated['end_time'], $timezone)->utc();
        }

        // Update appointment
        $appointment->update($validated);

        // Update reminders if provided
        if (isset($validated['reminders'])) {
            // Delete existing reminders
            $appointment->reminders()->delete();

            // Create new reminders
            foreach ($validated['reminders'] as $reminder) {
                $appointment->reminders()->create($reminder);
            }

            // Reschedule all reminders
            $appointment->scheduleReminders();
        }

        return response()->json(
            $appointment->load(['client', 'reminders', 'reminderDispatches'])
        );
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $appointment->delete();

        return response()->json(null, 204);
    }
} 