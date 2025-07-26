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
    public function createPayment($data){
        $chatId = $data['chat_id'];
        $order=Order::where('chat_id','=',$chatId)->where('status','pending')->first();
        if($order){
            $order->update([
               'no_of_photos'=>$data['no_of_photos'],
               'payment_status'=>'initialized',
               'total_amount'=>$data['total_amount'],
               //set deliver date to current date
               'delivery_date'=>date('Y-m-d'),
            ]);
        }
        return $order;
        // return Order::create($data);
    }
}
