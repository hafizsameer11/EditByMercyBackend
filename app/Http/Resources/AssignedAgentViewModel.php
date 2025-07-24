<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedAgentViewModel extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'chat_id' => $this->id ?? null,
            'type' => $this->type ?? null,

            'user' => [
                'id' => $this->participantA?->id ?? null,
                'name' => $this->participantA?->name ?? null,
                'profile_picture' => $this->participantA?->profile_picture ?? null,
            ],

            'agent' => [
                'id' => $this->agent?->id ?? null,
                'name' => $this->agent?->name ?? null,
                'profile_picture' => $this->agent?->profile_picture ?? null,
            ],

            'messages' => $this->messages?->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'type' => $msg->type,
                    'message' => $msg->message,
                    'file' => $msg->file,
                    'sender_id' => $msg->sender_id,
                    'created_at' => $msg->created_at->toDateTimeString(),
                ];
            }),

            'order' => $this->order ? [
                'id' => $this->order->id ?? null,
                'status' => $this->order->status ?? null,
                'amount' => $this->order->total_amount ?? null,
                'service_type' => $this->order->service_type ?? null,
                'payment_status' => $this->order->payment_status ?? null,
                'delivery_date' => $this->order->delivery_date ?? null,
            ] : null,
        ];
    }
}
