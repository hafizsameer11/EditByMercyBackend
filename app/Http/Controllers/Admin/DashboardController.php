<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics and data
     */
    public function index(Request $request)
    {
        try {
            // 1. Total Users (only users with role = 'user')
            $totalUsers = User::where('role', 'user')->count();

            // 2. Amount Generated (sum of all completed orders)
            $amountGenerated = Order::where('payment_status', 'success')
                ->sum('total_amount');

            // 3. Active Orders (pending or processing)
            $activeOrders = Order::whereIn('status', ['pending', 'processing'])->count();

            // 4. Completed Orders
            $completedOrders = Order::where('status', 'success')->count();

            // 5. Active Agents Chats (recent chats with latest message)
            $activeChats = Chat::with([
                'participantA:id,name,profile_picture',
                'participantB:id,name,profile_picture',
                'order:id,chat_id,service_type',
                'messages' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
                ->where('type', 'user-agent')
                ->whereHas('messages', function ($query) {
                    // Only chats with messages in last 7 days
                    $query->where('created_at', '>=', now()->subDays(7));
                })
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($chat) {
                    $lastMessage = $chat->messages->first();
                    return [
                        'id' => $chat->id,
                        'user' => [
                            'id' => $chat->participantA->id ?? null,
                            'name' => $chat->participantA->name ?? 'Unknown',
                            'profile_picture' => $chat->participantA->profile_picture ?? null,
                        ],
                        'service_type' => $chat->order->service_type ?? 'General',
                        'last_message' => $lastMessage ? [
                            'text' => $lastMessage->message ?? 'Media',
                            'created_at' => $lastMessage->created_at->format('M d, Y - h:i A'),
                        ] : null,
                    ];
                });

            // 6. Recent Orders (for the orders table)
            $recentOrders = Order::with([
                'user:id,name,profile_picture',
                'agent:id,name,profile_picture',
                'chat:id'
            ])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'customer' => [
                            'id' => $order->user->id ?? null,
                            'name' => $order->user->name ?? 'Unknown',
                            'profile_picture' => $order->user->profile_picture ?? null,
                        ],
                        'service_name' => $order->service_type ?? 'N/A',
                        'amount' => $order->total_amount ? 'N' . number_format($order->total_amount, 2) : 'N0.00',
                        'editor' => [
                            'id' => $order->agent->id ?? null,
                            'name' => $order->agent->name ?? 'Unassigned',
                            'profile_picture' => $order->agent->profile_picture ?? null,
                        ],
                        'date' => $order->created_at->format('m/d/y - h:i A'),
                        'status' => $order->status ?? 'pending',
                        'chat_id' => $order->chat_id ?? null,
                    ];
                });

            // Prepare response data
            $dashboardData = [
                'stats' => [
                    'total_users' => $totalUsers,
                    'amount_generated' => 'N' . number_format($amountGenerated, 2),
                    'amount_generated_raw' => $amountGenerated,
                    'active_orders' => $activeOrders,
                    'completed_orders' => $completedOrders,
                ],
                'active_chats' => $activeChats,
                'recent_orders' => $recentOrders,
            ];

            return ResponseHelper::success($dashboardData, 'Dashboard data fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch dashboard data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get detailed analytics (users, orders, revenue) with time ranges
     */
    public function analytics(Request $request)
    {
        try {
            // Base totals
            $totalUsers = User::where('role', 'user')->count();

            $amountGenerated = Order::where('payment_status', 'success')
                ->sum('total_amount');

            // Time ranges
            $todayStart = now()->startOfDay();
            $todayEnd   = now()->endOfDay();

            $monthStart = now()->startOfMonth();
            $monthEnd   = now()->endOfMonth();

            $yearStart  = now()->startOfYear();
            $yearEnd    = now()->endOfYear();

            // Users analytics
            $usersToday = User::where('role', 'user')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count();

            $usersThisMonth = User::where('role', 'user')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $usersThisYear = User::where('role', 'user')
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->count();

            $usersByMonth = User::where('role', 'user')
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Orders analytics
            $ordersToday = Order::whereBetween('created_at', [$todayStart, $todayEnd])->count();
            $ordersThisMonth = Order::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $ordersThisYear = Order::whereBetween('created_at', [$yearStart, $yearEnd])->count();

            $ordersByMonth = Order::whereBetween('created_at', [$yearStart, $yearEnd])
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Revenue / Transactions analytics
            $revenueToday = Transaction::where('status', 'success')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->sum('amount');

            $revenueThisMonth = Transaction::where('status', 'success')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $revenueThisYear = Transaction::where('status', 'success')
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->sum('amount');

            $revenueByMonth = Transaction::where('status', 'success')
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Bottom cards (activity tab style)
            // We don't have soft deletes on users table, so treat "deleted accounts" as blocked users
            $deletedAccounts = User::where('role', 'user')
                ->where('is_blocked', true)
                ->count();

            $onlineUsers = User::where('role', 'user')
                ->whereNotNull('fcmToken')
                ->count();

            $activeUsers = User::whereHas('orders', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->count();

            $usersWithNoOrders = User::where('role', 'user')
                ->doesntHave('orders')
                ->count();
            $bounceRate = $totalUsers > 0 ? round(($usersWithNoOrders / $totalUsers) * 100) : 0;

            $data = [
                'analytics' => [
                    'users' => [
                        'today' => $usersToday,
                        'month' => $usersThisMonth,
                        'year' => $usersThisYear,
                        'by_month' => $usersByMonth,
                    ],
                    'orders' => [
                        'today' => $ordersToday,
                        'month' => $ordersThisMonth,
                        'year' => $ordersThisYear,
                        'by_month' => $ordersByMonth,
                    ],
                    'revenue' => [
                        'today' => $revenueToday,
                        'month' => $revenueThisMonth,
                        'year' => $revenueThisYear,
                        'by_month' => $revenueByMonth,
                    ],
                ],
                'activity_cards' => [
                    'total_users' => $totalUsers,
                    'online_users' => $onlineUsers,
                    'active_users' => $activeUsers,
                    'bounce_rate' => $bounceRate,
                    'deleted_accounts' => $deletedAccounts,
                    'total_revenue' => $amountGenerated,
                    'total_revenue_formatted' => 'N' . number_format($amountGenerated, 2),
                ],
            ];

            return ResponseHelper::success($data, 'Analytics data fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch analytics data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all orders with filtering and search
     */
    public function getOrders(Request $request)
    {
        try {
            $query = Order::with([
                'user:id,name,profile_picture',
                'agent:id,name,profile_picture',
                'chat:id'
            ]);

            // Search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('agent', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('service_type', 'like', "%{$search}%");
            }

            // Status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Service type filter
            if ($request->has('service_type') && $request->service_type) {
                $query->where('service_type', $request->service_type);
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform data
            $transformedOrders = $orders->getCollection()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer' => [
                        'id' => $order->user->id ?? null,
                        'name' => $order->user->name ?? 'Unknown',
                        'profile_picture' => $order->user->profile_picture ?? null,
                    ],
                    'service_name' => $order->service_type ?? 'N/A',
                    'amount' => $order->total_amount ? 'N' . number_format($order->total_amount, 2) : 'N0.00',
                    'amount_raw' => $order->total_amount,
                    'editor' => [
                        'id' => $order->agent->id ?? null,
                        'name' => $order->agent->name ?? 'Unassigned',
                        'profile_picture' => $order->agent->profile_picture ?? null,
                    ],
                    'date' => $order->created_at->format('m/d/y - h:i A'),
                    'status' => $order->status ?? 'pending',
                    'payment_status' => $order->payment_status ?? 'unpaid',
                    'chat_id' => $order->chat_id ?? null,
                    'delivery_date' => $order->delivery_date ?? null,
                    'no_of_photos' => $order->no_of_photos ?? null,
                ];
            });

            return ResponseHelper::success([
                'orders' => $transformedOrders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ], 'Orders fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch orders: ' . $e->getMessage(), 500);
        }
    }
}

