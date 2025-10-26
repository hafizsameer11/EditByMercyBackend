<?php

namespace App\Helpers;

use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a user activity
     *
     * @param string $activity The activity type/name
     * @param string|null $description Additional description
     * @param array $metadata Additional metadata to store
     * @param int|null $userId User ID (defaults to authenticated user)
     * @return UserActivity|null
     */
    public static function log(
        string $activity,
        ?string $description = null,
        array $metadata = [],
        ?int $userId = null
    ): ?UserActivity {
        try {
            $userId = $userId ?? Auth::id();

            if (!$userId) {
                return null;
            }

            return UserActivity::create([
                'user_id' => $userId,
                'activity' => $activity,
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::error('Failed to log user activity: ' . $e->getMessage(), [
                'activity' => $activity,
                'user_id' => $userId,
            ]);
            return null;
        }
    }

    /**
     * Log message sent activity
     */
    public static function logMessageSent(int $chatId, string $messageType, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'message_sent',
            "Sent a {$messageType} message",
            ['chat_id' => $chatId, 'message_type' => $messageType],
            $userId
        );
    }

    /**
     * Log message deleted activity
     */
    public static function logMessageDeleted(int $messageId, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'message_deleted',
            'Deleted a message',
            ['message_id' => $messageId],
            $userId
        );
    }

    /**
     * Log message edited activity
     */
    public static function logMessageEdited(int $messageId, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'message_edited',
            'Edited a message',
            ['message_id' => $messageId],
            $userId
        );
    }

    /**
     * Log message forwarded activity
     */
    public static function logMessageForwarded(int $messageId, int $receiverId, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'message_forwarded',
            'Forwarded a message',
            ['message_id' => $messageId, 'receiver_id' => $receiverId],
            $userId
        );
    }

    /**
     * Log chat deleted activity
     */
    public static function logChatDeleted(int $chatId, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'chat_deleted',
            'Deleted a chat',
            ['chat_id' => $chatId],
            $userId
        );
    }

    /**
     * Log order created activity
     */
    public static function logOrderCreated(int $orderId, string $serviceType, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'order_created',
            "Created order for {$serviceType}",
            ['order_id' => $orderId, 'service_type' => $serviceType],
            $userId
        );
    }

    /**
     * Log order status updated activity
     */
    public static function logOrderStatusUpdated(int $orderId, string $status, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'order_status_updated',
            "Updated order status to {$status}",
            ['order_id' => $orderId, 'status' => $status],
            $userId
        );
    }

    /**
     * Log payment created activity
     */
    public static function logPaymentCreated(int $orderId, float $amount, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'payment_created',
            "Created payment of \${$amount}",
            ['order_id' => $orderId, 'amount' => $amount],
            $userId
        );
    }

    /**
     * Log payment status updated activity
     */
    public static function logPaymentStatusUpdated(int $orderId, string $status, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'payment_status_updated',
            "Updated payment status to {$status}",
            ['order_id' => $orderId, 'status' => $status],
            $userId
        );
    }

    /**
     * Log login activity
     */
    public static function logLogin(?int $userId = null): ?UserActivity
    {
        return self::log(
            'login',
            'User logged in',
            [],
            $userId
        );
    }

    /**
     * Log logout activity
     */
    public static function logLogout(?int $userId = null): ?UserActivity
    {
        return self::log(
            'logout',
            'User logged out',
            [],
            $userId
        );
    }

    /**
     * Log registration activity
     */
    public static function logRegistration(?int $userId = null): ?UserActivity
    {
        return self::log(
            'registration',
            'User registered',
            [],
            $userId
        );
    }

    /**
     * Log profile update activity
     */
    public static function logProfileUpdate(array $updatedFields = [], ?int $userId = null): ?UserActivity
    {
        return self::log(
            'profile_updated',
            'Updated profile information',
            ['updated_fields' => $updatedFields],
            $userId
        );
    }

    /**
     * Log password change activity
     */
    public static function logPasswordChange(?int $userId = null): ?UserActivity
    {
        return self::log(
            'password_changed',
            'Changed password',
            [],
            $userId
        );
    }

    /**
     * Log chat created activity
     */
    public static function logChatCreated(int $chatId, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'chat_created',
            'Created a new chat',
            ['chat_id' => $chatId],
            $userId
        );
    }

    /**
     * Log file downloaded activity
     */
    public static function logFileDownloaded(int $messageId, ?int $userId = null): ?UserActivity
    {
        return self::log(
            'file_downloaded',
            'Downloaded a file',
            ['message_id' => $messageId],
            $userId
        );
    }
}

