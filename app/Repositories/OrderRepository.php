<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    // This class will handle order-related database operations
    // For example, creating, updating, and retrieving orders

    public function createOrder($data)
    {
        return Order::create($data);
    }

    public function getOrderById($id)
    {
    }
}
