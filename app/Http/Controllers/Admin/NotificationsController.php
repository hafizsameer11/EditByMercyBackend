<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationsController extends Controller
{
    /**
     * Get all sent notifications
     */
    public function index(Request $request)
    {
        try {
            $query = Notification::with('user:id,name,profile_picture')
                ->orderBy('created_at', 'desc');

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $perPage = $request->get('per_page', 20);
            $notifications = $query->paginate($perPage);

            $transformedNotifications = $notifications->getCollection()->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'user' => [
                        'id' => $notification->user->id ?? null,
                        'name' => $notification->user->name ?? 'All Users',
                        'profile_picture' => $notification->user->profile_picture ?? null,
                    ],
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'is_read' => $notification->is_read ?? false,
                    'created_at' => $notification->created_at->format('m/d/y - h:i A'),
                ];
            });

            return ResponseHelper::success([
                'notifications' => $transformedNotifications,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ]
            ], 'Notifications fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send new notification
     */
    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'image' => 'nullable|image|max:5120', // 5MB max
                'recipient_type' => 'required|in:all,specific',
                'user_ids' => 'required_if:recipient_type,specific|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // Handle image upload if provided
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('notifications', 'public');
            }

            // Determine recipients
            $recipients = [];
            if ($request->recipient_type === 'all') {
                $recipients = User::where('role', 'user')->get();
            } else {
                $recipients = User::whereIn('id', $request->user_ids)->get();
            }

            $sentCount = 0;
            $failedCount = 0;

            // Send notifications to each recipient
            foreach ($recipients as $user) {
                try {
                    // Create notification in database
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => $request->subject,
                        'content' => $request->message,
                    ]);

                    // Send push notification via Firebase
                    NotificationService::sendToUserById(
                        $user->id,
                        $request->subject,
                        $request->message
                    );

                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            return ResponseHelper::success([
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_recipients' => count($recipients),
                'image_path' => $imagePath ? asset('storage/' . $imagePath) : null,
            ], 'Notifications sent successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to send notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get notification templates/suggestions
     */
    public function getTemplates()
    {
        try {
            $templates = [
                [
                    'id' => 1,
                    'name' => 'Welcome Message',
                    'subject' => 'Welcome to Edit by Mercy',
                    'message' => 'Thank you for joining Edit by Mercy. We\'re excited to help you transform your photos!',
                ],
                [
                    'id' => 2,
                    'name' => 'Order Completed',
                    'subject' => 'Your Order is Complete',
                    'message' => 'Great news! Your photo editing order has been completed and is ready for download.',
                ],
                [
                    'id' => 3,
                    'name' => 'Special Offer',
                    'subject' => 'Special Discount Just for You!',
                    'message' => 'Get 20% off on your next order. Limited time offer!',
                ],
                [
                    'id' => 4,
                    'name' => 'Payment Reminder',
                    'subject' => 'Payment Pending',
                    'message' => 'You have a pending payment for your order. Please complete the payment to proceed.',
                ],
            ];

            return ResponseHelper::success($templates, 'Templates fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch templates: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return ResponseHelper::success(null, 'Notification deleted successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all users for notification targeting
     */
    public function getUsers(Request $request)
    {
        try {
            $query = User::where('role', 'user');

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->select('id', 'name', 'email', 'profile_picture')
                ->limit(50)
                ->get();

            return ResponseHelper::success($users, 'Users fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch users: ' . $e->getMessage(), 500);
        }
    }
}


