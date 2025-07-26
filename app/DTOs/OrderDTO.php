<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderDTO
{
    public function __construct(
        public ?int $user_id = null,
        public ?int $agent_id = null,
        public ?string $service_type = null,
        public ?float $total_amount = null,
        public ?string $payment_method = null,
        public ?string $no_of_photos = null,
        public ?string $delivery_date = null,
        public ?string $status = null,
        public ?string $payment_status = null,
        public ?string $chat_id = null,
    ) {}

    public static function fromInitialRequest(Request $request): self
    {
        return new self(
            user_id: Auth::id(),
            agent_id: $request->input('agent_id'),
            service_type: $request->input('service_type'),
            chat_id: $request->input('chat_id'),
        );
    }

    public static function fromUpdateRequest(Request $request): self
    {
        return new self(
            agent_id: $request->input('agent_id'),
            service_type: $request->input('service_type'),
            total_amount: $request->input('total_amount'),
            payment_method: $request->input('payment_method'),
            no_of_photos: $request->input('no_of_photos'),
            delivery_date: $request->input('delivery_date'),
            status: $request->input('status'),
            payment_status: $request->input('payment_status'),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn($value) => !is_null($value));
    }
    public function withAgentId(int $agentId): self
    {
        $this->agent_id = $agentId;
        return $this;
    }
    public static function fromAgentUpdate(Request $request): self
    {
        return new self(
            total_amount: $request->input('total_amount'),
            no_of_photos: $request->input('no_of_photos'),
            delivery_date: $request->input('delivery_date'),
        );
    }
    public static function getPaymentDetails(Request $request): self{
        return new self(
             total_amount: $request->input('total_amount'),
            no_of_photos: $request->input('no_of_photos'),
            delivery_date: $request->input('delivery_date'),
        );

    }

    public static function fromUserPayment(Request $request): self
    {
        return new self(
            payment_status: 'paid',
            payment_method: $request->input('payment_method'),
            status: 'processing' // or "confirmed", etc.
        );
    }
}
