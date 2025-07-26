<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\Repositories\OrderRepository;
use Exception;

class OrderService
{
    protected $orderRepository;
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    public function createOrder(OrderDTO $orderDTO)
    {
        try {
            $data = $orderDTO->toArray();
            $order = $this->orderRepository->createOrder($data);
            return $order;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function createPayment(array $data){
        try {
            $payment = $this->orderRepository->createPayment($data);
            return $payment;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
