<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $firebaseNotificationService;

    public function __construct(FirebaseNotificationService $firebaseNotificationService)
    {
        $this->firebaseNotificationService = $firebaseNotificationService;
    }

    /**
     * Send a notification to a specific user by their user ID.
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @return array
     */
    public static function  sendToUserById(int $userId, string $title, string $body): array
    {
        $firebaseNotificationService= new FirebaseNotificationService();
        $user = User::find($userId);
        Log::info("data received",[$userId,$title,$body]);  
        if (!$user || !$user->fcmToken) {
            Log::warning("User or FCM token not found for userId: $userId");
            return ['status' => 'error', 'message' => 'User or FCM token not found'];
        }
        Log::info("User fcm token $user->fcmToken");
        //conver userId to string
        $stringUserId = (string) $userId;
        try {
            $response = $firebaseNotificationService->sendNotification(
                $user->fcmToken,
                $title,
                $body,
                $stringUserId 
            );

            Log::info("Notification sent to userId: $userId", $response);
            $notification=Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'content' => $body
            ]);
            return ['status' => 'success', 'message' => 'Notification sent successfully', 'response' => $response];
        } catch (\Exception $e) {
            Log::error("Error sending notification to userId: $userId - " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to send notification', 'error' => $e->getMessage()];
        }
    }
}
