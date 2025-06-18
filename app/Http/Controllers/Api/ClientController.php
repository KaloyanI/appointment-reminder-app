<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
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