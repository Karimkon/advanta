<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    /**
     * Register a device token for push notifications
     */
    public function register(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'device_type' => 'required|in:ios,android,web',
        ]);

        $user = Auth::user();

        // Check if token already exists for any user
        $existingToken = DeviceToken::where('device_token', $request->device_token)->first();

        if ($existingToken) {
            // If token belongs to a different user, reassign it
            if ($existingToken->tokenable_id !== $user->id || $existingToken->tokenable_type !== get_class($user)) {
                $existingToken->update([
                    'tokenable_type' => get_class($user),
                    'tokenable_id' => $user->id,
                    'device_type' => $request->device_type,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);
            } else {
                // Just update the existing token
                $existingToken->update([
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);
            }
        } else {
            // Create new token
            DeviceToken::create([
                'tokenable_type' => get_class($user),
                'tokenable_id' => $user->id,
                'device_token' => $request->device_token,
                'device_type' => $request->device_type,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully',
        ]);
    }

    /**
     * Unregister a device token
     */
    public function unregister(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = Auth::user();

        // Find and deactivate the token
        DeviceToken::where('device_token', $request->device_token)
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Device token unregistered successfully',
        ]);
    }
}
