<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Get all users with stats and filtering
     */
    public function index(Request $request)
    {
        try {
            // Calculate stats
            $totalUsers = User::where('role', 'user')->count();
            $onlineUsers = User::where('role', 'user')
                ->whereNotNull('fcmToken')
                ->count();
            $activeUsers = User::where('role', 'user')
                ->whereHas('orders', function ($query) {
                    $query->whereIn('status', ['pending', 'processing']);
                })
                ->count();

            // Build query
            $query = User::where('role', 'user')
                ->withCount('orders');

            // Online/Offline filter
            if ($request->has('status')) {
                switch ($request->status) {
                    case 'online':
                        $query->whereNotNull('fcmToken');
                        break;
                    case 'offline':
                        $query->whereNull('fcmToken');
                        break;
                }
            }

            // Search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Date filter
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $users = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform data
            $transformedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'N/A',
                    'profile_picture' => $user->profile_picture,
                    'no_of_orders' => $user->orders_count ?? 0,
                    'date_registered' => $user->created_at->format('m/d/y - h:i A'),
                    'is_online' => !empty($user->fcmToken),
                    'is_blocked' => $user->is_blocked ?? false,
                    'is_verified' => $user->is_verified ?? false,
                ];
            });

            return ResponseHelper::success([
                'stats' => [
                    'total_users' => $totalUsers,
                    'online_users' => $onlineUsers,
                    'active_users' => $activeUsers,
                ],
                'users' => $transformedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ], 'Users fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single user details with full information
     */
    public function show($id)
    {
        try {
            $user = User::with(['orders'])->withCount('orders')->findOrFail($id);

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? 'N/A',
                'profile_picture' => $user->profile_picture,
                'role' => $user->role,
                'is_online' => !empty($user->fcmToken),
                'is_blocked' => $user->is_blocked ?? false,
                'is_verified' => $user->is_verified ?? false,
                'no_of_orders' => $user->orders_count ?? 0,
                'date_registered' => $user->created_at->format('m/d/y - h:i A'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return ResponseHelper::success($data, 'User details fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('User not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Create new user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:6',
                'profile_picture' => 'nullable|image|max:5120', // 5MB max
                'role' => 'nullable|in:admin,support,editor,chief_editor,user',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
            ];

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile_picture', 'public');
                $userData['profile_picture'] = $path;
            }

            $user = User::create($userData);

            return ResponseHelper::success($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:6',
                'profile_picture' => 'nullable|image|max:5120', // 5MB max
                'role' => 'nullable|in:admin,support,editor,chief_editor,user',
                'is_blocked' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // Update basic fields
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('role')) $user->role = $request->role;
            if ($request->has('is_blocked')) $user->is_blocked = $request->is_blocked;

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old picture
                if ($user->getRawOriginal('profile_picture') && Storage::disk('public')->exists($user->getRawOriginal('profile_picture'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('profile_picture'));
                }
                
                $path = $request->file('profile_picture')->store('profile_picture', 'public');
                $user->profile_picture = $path;
            }

            $user->save();

            return ResponseHelper::success($user, 'User updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Delete profile picture if exists
            if ($user->getRawOriginal('profile_picture') && Storage::disk('public')->exists($user->getRawOriginal('profile_picture'))) {
                Storage::disk('public')->delete($user->getRawOriginal('profile_picture'));
            }

            $user->delete();

            return ResponseHelper::success(null, 'User deleted successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's chats
     */
    public function getUserChats($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            $query = Chat::where(function ($q) use ($id) {
                $q->where('user_id', $id)
                    ->orWhere('user_2_id', $id);
            })
                ->with([
                    'participantA:id,name,profile_picture',
                    'participantB:id,name,profile_picture',
                    'agent:id,name,profile_picture',
                    'order:id,chat_id,service_type,total_amount,status,no_of_photos,created_at',
                    'messages' => function ($q) {
                        $q->latest()->limit(1);
                    }
                ])
                ->withCount([
                    'messages as unread_count' => function ($query) use ($id) {
                        $query->where('is_read', 0)
                            ->where('receiver_id', $id);
                    }
                ]);

            // Filter by service type
            if ($request->has('service_type') && $request->service_type) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('service_type', $request->service_type);
                });
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $chats = $query->orderBy('updated_at', 'desc')->get();

            $transformedChats = $chats->map(function ($chat) use ($id) {
                $otherParticipant = $chat->user_id === $id ? $chat->participantB : $chat->participantA;
                $lastMessage = $chat->messages->first();

                return [
                    'id' => $chat->id,
                    'agent_name' => $otherParticipant->name ?? 'N/A',
                    'agent_profile' => $otherParticipant->profile_picture ?? null,
                    'service' => $chat->order->service_type ?? 'N/A',
                    'order_amount' => $chat->order ? 'N' . number_format($chat->order->total_amount, 2) : 'N0.00',
                    'no_of_photos' => $chat->order->no_of_photos ?? 0,
                    'date' => $chat->created_at->format('m/d/y - h:i A'),
                    'status' => $chat->order->status ?? 'N/A',
                    'unread_count' => $chat->unread_count ?? 0,
                    'has_questionnaire' => $chat->messages()->where('type', 'questionnaire')->exists(),
                ];
            });

            return ResponseHelper::success($transformedChats, 'User chats fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch user chats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's orders with stats
     */
    public function getUserOrders($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            // Calculate order stats
            $totalOrders = Order::where('user_id', $id)->count();
            $activeOrders = Order::where('user_id', $id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();
            $completedOrders = Order::where('user_id', $id)
                ->where('status', 'success')
                ->count();

            // Build query
            $query = Order::where('user_id', $id)
                ->with([
                    'agent:id,name,profile_picture',
                    'chat:id'
                ]);

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by service type
            if ($request->has('service_type') && $request->service_type) {
                $query->where('service_type', $request->service_type);
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $perPage = $request->get('per_page', 20);
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $transformedOrders = $orders->getCollection()->map(function ($order) use ($user) {
                return [
                    'id' => $order->id,
                    'agent_name' => $user->name,
                    'agent_profile' => $user->profile_picture,
                    'service_name' => $order->service_type ?? 'N/A',
                    'amount' => 'N' . number_format($order->total_amount, 2),
                    'amount_raw' => $order->total_amount,
                    'editor' => [
                        'id' => $order->agent->id ?? null,
                        'name' => $order->agent->name ?? 'Unassigned',
                        'profile_picture' => $order->agent->profile_picture ?? null,
                    ],
                    'date' => $order->created_at->format('m/d/y - h:i A'),
                    'status' => $order->status ?? 'pending',
                    'payment_status' => $order->payment_status ?? 'unpaid',
                    'chat_id' => $order->chat_id,
                    'no_of_photos' => $order->no_of_photos ?? 0,
                    'delivery_date' => $order->delivery_date ?? null,
                    'txn' => $order->txn ?? null,
                ];
            });

            return ResponseHelper::success([
                'stats' => [
                    'total_orders' => $totalOrders,
                    'active' => $activeOrders,
                    'completed' => $completedOrders,
                ],
                'orders' => $transformedOrders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ], 'User orders fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch user orders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's activity log
     */
    public function getUserActivity($id)
    {
        try {
            $user = User::findOrFail($id);

            // Get recent activities
            $activities = [];

            // Orders activity
            $orders = Order::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($orders as $order) {
                $activities[] = [
                    'type' => 'order',
                    'activity' => $user->name . ' created an order',
                    'details' => 'Service: ' . $order->service_type . ' - Amount: N' . number_format($order->total_amount, 2),
                    'date' => $order->created_at->format('m/d/y - h:i A'),
                    'timestamp' => $order->created_at,
                ];
            }

            // Sort by timestamp
            usort($activities, function ($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });

            // Remove timestamp from response
            $activities = array_map(function ($activity) {
                unset($activity['timestamp']);
                return $activity;
            }, $activities);

            return ResponseHelper::success($activities, 'User activity fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch user activity: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Block/Unblock user
     */
    public function toggleBlock($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_blocked = !$user->is_blocked;
            $user->save();

            $status = $user->is_blocked ? 'blocked' : 'unblocked';

            return ResponseHelper::success([
                'is_blocked' => $user->is_blocked
            ], "User {$status} successfully", 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to toggle block status: ' . $e->getMessage(), 500);
        }
    }
}

