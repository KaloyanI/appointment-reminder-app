<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => ['nullable', Rule::in(['name', 'email', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Auth::user()->clients();

        // Apply search if provided
        if ($request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            });
        }

        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $clients = $query->paginate($request->per_page ?? 15);

        return response()->json($clients);
    }

    /**
     * Store a new client.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'phone' => 'nullable|string|max:20',
            'timezone' => 'required|timezone',
            'preferred_notification_method' => ['required', Rule::in(['email', 'sms', 'both'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        $client = Auth::user()->clients()->create($validated);

        return response()->json($client, 201);
    }

    /**
     * Display the specified client.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function show(Client $client): JsonResponse
    {
        if ($client->user_id !== Auth::id()) {
            return response()->json(['message' => 'This client does not belong to you.'], 403);
        }

        return response()->json($client->load('appointments'));
    }

    /**
     * Update the specified client.
     *
     * @param Request $request
     * @param Client $client
     * @return JsonResponse
     */
    public function update(Request $request, Client $client): JsonResponse
    {
        if ($client->user_id !== Auth::id()) {
            return response()->json(['message' => 'This client does not belong to you.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('clients')->where(function ($query) use ($client) {
                    return $query->where('user_id', Auth::id())
                        ->whereNot('id', $client->id);
                }),
            ],
            'phone' => 'nullable|string|max:20',
            'timezone' => 'sometimes|required|timezone',
            'preferred_notification_method' => ['sometimes', 'required', Rule::in(['email', 'sms', 'both'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * Remove the specified client.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function destroy(Client $client): JsonResponse
    {
        if ($client->user_id !== Auth::id()) {
            return response()->json(['message' => 'This client does not belong to you.'], 403);
        }

        $client->delete();

        return response()->json(null, 204);
    }

    /**
     * Update the client's notification preferences.
     *
     * @param Request $request
     * @param Client $client
     * @return JsonResponse
     */
    public function updateNotificationPreferences(Request $request, Client $client): JsonResponse
    {
        if ($client->user_id !== Auth::id()) {
            return response()->json(['message' => 'This client does not belong to you.'], 403);
        }

        $validated = $request->validate([
            'preferred_notification_method' => 'required|in:email,sms,both',
        ]);

        $client->update($validated);

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'data' => $client
        ]);
    }
} 