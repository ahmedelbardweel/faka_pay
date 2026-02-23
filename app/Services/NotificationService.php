<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a Push Notification via FCM.
     *
     * @param string|null $fcmToken
     * @param string $title
     * @param string $body
     * @param array $data Additional data for the app
     * @return bool
     */
    public function sendPush(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!$fcmToken) {
            Log::warning("FCM Notification skipped: No token provided.");
            return false;
        }

        $serverKey = env('FCM_SERVER_KEY');
        if (!$serverKey) {
            Log::error("FCM Notification failed: FCM_SERVER_KEY not set in .env.");
            return false;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';

        $payload = [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info("FCM Notification sent successfully to token: " . substr($fcmToken, 0, 10) . "...");
                return true;
            }

            Log::error("FCM Notification failed. Response: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("FCM Notification error: " . $e->getMessage());
            return false;
        }
    }
}
