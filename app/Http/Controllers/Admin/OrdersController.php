<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    /**
     * Get all orders with stats and filtering
     */
    public function index(Request $request)
    {
        try {
            // Calculate stats
            $totalOrders = Order::count();
            $activeOrders = Order::whereIn('status', ['pending', 'processing'])->count();
            $completedOrders = Order::where('status', 'success')->count();

            // Build query
            $query = Order::with([
                'user:id,name,profile_picture',
                'agent:id,name,profile_picture',
                'chat:id'
            ]);

            // Filter by status
            if ($request->has('status') && $request->status && $request->status !== 'All') {
                if ($request->status === 'Pending') {
                    $query->where('status', 'pending');
                } elseif ($request->status === 'Photo Editing') {
                    $query->where('service_type', 'Photo Editing');
                } elseif ($request->status === 'Photo Manipulation') {
                    $query->where('service_type', 'Photo Manipulation');
                } elseif ($request->status === 'Body Reshaping') {
                    $query->where('service_type', 'Body Reshaping');
                } elseif ($request->status === 'Completed') {
                    $query->where('status', 'success');
                } elseif ($request->status === 'Failed') {
                    $query->where('status', 'failed');
                } else {
                    $query->where('status', strtolower($request->status));
                }
            }

            // Filter by service type
            if ($request->has('service_type') && $request->service_type && $request->service_type !== 'All') {
                $query->where('service_type', $request->service_type);
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('agent', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $transformedOrders = $orders->getCollection()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer' => [
                        'id' => $order->user->id ?? null,
                        'name' => $order->user->name ?? 'Unknown',
                        'profile_picture' => $order->user->profile_picture ?? null,
                    ],
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
            ], 'Orders fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch orders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single order details
     */
    public function show($id)
    {
        try {
            $order = Order::with([
                'user:id,name,profile_picture,email,phone',
                'agent:id,name,profile_picture,email',
                'chat:id'
            ])->findOrFail($id);

            $data = [
                'id' => $order->id,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'agent_name' => $order->user->name ?? 'N/A',
                'agent_profile' => $order->user->profile_picture ?? null,
                'category' => $order->service_type ?? 'N/A',
                'no_of_photos' => $order->no_of_photos ?? 0,
                'amount_paid' => 'N' . number_format($order->total_amount, 2),
                'amount_paid_raw' => $order->total_amount,
                'txn_id' => $order->txn ?? 'N/A',
                'delivery_date' => $order->delivery_date ?? 'N/A',
                'created_at' => $order->created_at->format('m/d/y - h:i A'),
                'customer' => [
                    'id' => $order->user->id ?? null,
                    'name' => $order->user->name ?? 'Unknown',
                    'email' => $order->user->email ?? 'N/A',
                    'phone' => $order->user->phone ?? 'N/A',
                    'profile_picture' => $order->user->profile_picture ?? null,
                ],
                'editor' => [
                    'id' => $order->agent->id ?? null,
                    'name' => $order->agent->name ?? 'Unassigned',
                    'email' => $order->agent->email ?? 'N/A',
                    'profile_picture' => $order->agent->profile_picture ?? null,
                ],
                'chat_id' => $order->chat_id,
            ];

            return ResponseHelper::success($data, 'Order details fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Order not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,processing,success,failed',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $order = Order::findOrFail($id);
            $order->status = $request->status;
            $order->save();

            return ResponseHelper::success([
                'order' => $order,
                'status' => $order->status
            ], 'Order status updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update order status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update order payment status
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_status' => 'required|in:unpaid,initialized,success,failed',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $order = Order::findOrFail($id);
            $order->payment_status = $request->payment_status;
            $order->save();

            return ResponseHelper::success([
                'order' => $order,
                'payment_status' => $order->payment_status
            ], 'Payment status updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update payment status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update order details
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_type' => 'sometimes|string',
                'total_amount' => 'sometimes|numeric|min:0',
                'no_of_photos' => 'sometimes|integer|min:0',
                'delivery_date' => 'sometimes|date',
                'agent_id' => 'sometimes|exists:users,id',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $order = Order::findOrFail($id);

            if ($request->has('service_type')) $order->service_type = $request->service_type;
            if ($request->has('total_amount')) $order->total_amount = $request->total_amount;
            if ($request->has('no_of_photos')) $order->no_of_photos = $request->no_of_photos;
            if ($request->has('delivery_date')) $order->delivery_date = $request->delivery_date;
            if ($request->has('agent_id')) $order->agent_id = $request->agent_id;

            $order->save();

            return ResponseHelper::success($order, 'Order updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete order
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return ResponseHelper::success(null, 'Order deleted successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk update orders
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'action' => 'required|in:update_status,update_payment_status,delete',
                'status' => 'required_if:action,update_status|in:pending,processing,success,failed',
                'payment_status' => 'required_if:action,update_payment_status|in:unpaid,initialized,success,failed',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $orderIds = $request->order_ids;
            $action = $request->action;

            switch ($action) {
                case 'update_status':
                    Order::whereIn('id', $orderIds)->update(['status' => $request->status]);
                    break;
                case 'update_payment_status':
                    Order::whereIn('id', $orderIds)->update(['payment_status' => $request->payment_status]);
                    break;
                case 'delete':
                    Order::whereIn('id', $orderIds)->delete();
                    break;
            }

            return ResponseHelper::success([
                'affected_count' => count($orderIds),
                'action' => $action
            ], 'Bulk action completed successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to perform bulk action: ' . $e->getMessage(), 500);
        }
    }
}

