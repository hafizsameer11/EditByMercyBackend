<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionsController extends Controller
{
    /**
     * Get all transactions with stats and filtering
     */
    public function index(Request $request)
    {
        try {
            // Calculate stats
            $totalTransactions = Transaction::count();
            $completedTransactions = Transaction::where('status', 'completed')->count();
            $totalAmount = Transaction::where('status', 'completed')->sum('amount');

            // Build query - get transactions with order and user info
            $query = Transaction::with([
                'order' => function ($q) {
                    $q->with(['user:id,name,profile_picture', 'agent:id,name,profile_picture']);
                }
            ]);

            // Filter by status
            if ($request->has('status') && $request->status && $request->status !== 'All') {
                $query->where('status', strtolower($request->status));
            }

            // Filter by service type
            if ($request->has('service_type') && $request->service_type && $request->service_type !== 'All') {
                $query->where('service_type', $request->service_type);
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            // Search by customer name or service
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('order.user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform data
            $transformedTransactions = $transactions->getCollection()->map(function ($transaction) {
                $order = $transaction->order;
                $customer = $order ? $order->user : null;

                return [
                    'id' => $transaction->id,
                    'customer_name' => $customer ? $customer->name : 'Unknown',
                    'customer_profile' => $customer ? $customer->profile_picture : null,
                    'service_name' => $transaction->service_type ?? 'N/A',
                    'amount' => 'N' . number_format($transaction->amount, 2),
                    'amount_raw' => $transaction->amount,
                    'date' => $transaction->created_at->format('m/d/y - h:i A'),
                    'status' => $transaction->status ?? 'pending',
                    'order_id' => $transaction->order_id,
                ];
            });

            return ResponseHelper::success([
                'stats' => [
                    'total_transactions' => $totalTransactions,
                    'completed_transactions' => $completedTransactions,
                    'total_amount' => 'N' . number_format($totalAmount, 2),
                    'total_amount_raw' => $totalAmount,
                ],
                'transactions' => $transformedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ], 'Transactions fetched successfully', 200);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions: ' . $e->getMessage());
            return ResponseHelper::error('Failed to fetch transactions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single transaction details
     */
    public function show($id)
    {
        try {
            $transaction = Transaction::with([
                'order' => function ($q) {
                    $q->with(['user:id,name,email,phone,profile_picture', 'agent:id,name,profile_picture']);
                }
            ])->findOrFail($id);

            $order = $transaction->order;
            $customer = $order ? $order->user : null;

            $data = [
                'id' => $transaction->id,
                'customer' => [
                    'id' => $customer->id ?? null,
                    'name' => $customer->name ?? 'Unknown',
                    'email' => $customer->email ?? 'N/A',
                    'phone' => $customer->phone ?? 'N/A',
                    'profile_picture' => $customer->profile_picture ?? null,
                ],
                'service_type' => $transaction->service_type,
                'amount' => 'N' . number_format($transaction->amount, 2),
                'amount_raw' => $transaction->amount,
                'status' => $transaction->status,
                'order_id' => $transaction->order_id,
                'created_at' => $transaction->created_at->format('m/d/y - h:i A'),
            ];

            return ResponseHelper::success($data, 'Transaction details fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Transaction not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,completed,failed',
            ]);

            $transaction = Transaction::findOrFail($id);
            $transaction->status = $request->status;
            $transaction->save();

            return ResponseHelper::success([
                'transaction' => $transaction,
                'status' => $transaction->status
            ], 'Transaction status updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update transaction status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export transactions (returns data for export)
     */
    public function export(Request $request)
    {
        try {
            $query = Transaction::with([
                'order' => function ($q) {
                    $q->with(['user:id,name,email,phone', 'agent:id,name']);
                }
            ]);

            // Apply same filters as index
            if ($request->has('status') && $request->status && $request->status !== 'All') {
                $query->where('status', strtolower($request->status));
            }

            if ($request->has('service_type') && $request->service_type && $request->service_type !== 'All') {
                $query->where('service_type', $request->service_type);
            }

            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $transactions = $query->orderBy('created_at', 'desc')->get();

            $exportData = $transactions->map(function ($transaction) {
                $order = $transaction->order;
                $customer = $order ? $order->user : null;

                return [
                    'Transaction ID' => $transaction->id,
                    'Customer Name' => $customer ? $customer->name : 'Unknown',
                    'Customer Email' => $customer ? $customer->email : 'N/A',
                    'Customer Phone' => $customer ? $customer->phone : 'N/A',
                    'Service Type' => $transaction->service_type,
                    'Amount' => $transaction->amount,
                    'Status' => ucfirst($transaction->status),
                    'Date' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return ResponseHelper::success([
                'transactions' => $exportData,
                'count' => $exportData->count()
            ], 'Transactions exported successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to export transactions: ' . $e->getMessage(), 500);
        }
    }
}


