<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return ResponseHelper::success($orders, "Orders fetched successfully.");
        // $orders=Order::;
    }
    public function orderDetails($id)
    {
        $order = Order::find($id);
        if ($order) {
            return ResponseHelper::success($order, "Order details fetched successfully.");
        } else {
            return ResponseHelper::error("Order not found.");
        }
    }
}
