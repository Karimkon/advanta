<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseNotificationService
{
    private $projectId;
    private $serviceAccountPath;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->serviceAccountPath = storage_path('app/firebase/service-account.json');
    }

    /**
     * Get OAuth 2.0 access token for Firebase
     */
    private function getAccessToken(): ?string
    {
        try {
            // Cache the access token for 50 minutes (it expires in 60)
            return Cache::remember('firebase_access_token', 3000, function () {
                if (!file_exists($this->serviceAccountPath)) {
                    Log::error('Firebase service account file not found');
                    return null;
                }

                $credentials = new ServiceAccountCredentials(
                    ['https://www.googleapis.com/auth/firebase.messaging'],
                    $this->serviceAccountPath
                );

                $token = $credentials->fetchAuthToken();
                return $token['access_token'] ?? null;
            });
        } catch (\Exception $e) {
            Log::error('Error getting Firebase access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification to a single device
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        return $this->send([$deviceToken], $title, $body, $data);
    }

    /**
     * Send notification to multiple devices
     */
    public function send(array $deviceTokens, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('Could not get Firebase access token');
            return false;
        }

        $successCount = 0;
        $failedTokens = [];

        foreach ($deviceTokens as $token) {
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data), // Ensure all values are strings
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'advanta_notifications',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $title,
                                    'body' => $body,
                                ],
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            try {
                $response = Http::withToken($accessToken)
                    ->post(
                        "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                        $message
                    );

                if ($response->successful()) {
                    $successCount++;
                    // Update last_used_at
                    DeviceToken::where('device_token', $token)->update(['last_used_at' => now()]);
                } else {
                    $error = $response->json();
                    Log::warning('FCM send failed for token', [
                        'token' => substr($token, 0, 20) . '...',
                        'error' => $error,
                    ]);

                    // If token is invalid, mark it as inactive
                    if (isset($error['error']['details'])) {
                        foreach ($error['error']['details'] as $detail) {
                            if (isset($detail['errorCode']) && $detail['errorCode'] === 'UNREGISTERED') {
                                DeviceToken::where('device_token', $token)->update(['is_active' => false]);
                                $failedTokens[] = $token;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Exception sending FCM notification: ' . $e->getMessage());
            }
        }

        Log::info("FCM notifications sent: {$successCount}/" . count($deviceTokens) . " successful");
        return $successCount > 0;
    }

    /**
     * Send notification to a user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $tokens = $user->activeDeviceTokens()->pluck('device_token')->toArray();

        if (empty($tokens)) {
            Log::info("No active device tokens for user {$user->id}");
            return false;
        }

        return $this->send($tokens, $title, $body, $data);
    }

    /**
     * Send notification to a client
     */
    public function sendToClient(Client $client, string $title, string $body, array $data = []): bool
    {
        $tokens = $client->activeDeviceTokens()->pluck('device_token')->toArray();

        if (empty($tokens)) {
            Log::info("No active device tokens for client {$client->id}");
            return false;
        }

        return $this->send($tokens, $title, $body, $data);
    }

    /**
     * Send notification to users with a specific role
     */
    public function sendToRole(string $role, string $title, string $body, array $data = []): bool
    {
        $tokens = DeviceToken::whereHasMorph('tokenable', [User::class], function ($query) use ($role) {
            $query->where('role', $role);
        })->active()->pluck('device_token')->toArray();

        if (empty($tokens)) {
            Log::info("No active device tokens for role {$role}");
            return false;
        }

        return $this->send($tokens, $title, $body, $data);
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return false;
        }

        $message = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                    $message
                );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Exception sending FCM topic notification: ' . $e->getMessage());
            return false;
        }
    }
}
