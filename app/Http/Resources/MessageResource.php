<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

// ADD these if you don't already have them imported
use App\Models\Message;
use App\Models\Order;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        // -------- Resolve payment order for forwarded messages --------
        $paymentOrder = null;

        if ($this->is_forwarded && $this->original_id) {
            // Prefer eager-loaded relation to avoid extra queries
            $original = $this->relationLoaded('originalMessage')
                ? $this->originalMessage
                : Message::select('id', 'chat_id')->find($this->original_id);

            $chatId = $original?->chat_id;

            if ($chatId) {
                // If you have a direct relation Order->chat_id:
                $paymentOrder = Order::where('chat_id', $chatId)
                    ->latest('id')       // or ->latest() per your ordering rule
                    ->first();
            }
        }

        return [
            'id'           => $this->id ?? null,
            'chat_id'      => $this->chat_id ?? null,
            'sender_id'    => $this->sender_id ?? null,
            'receiver_id'  => $this->receiver_id ?? null,
            'type'         => $this->type ?? null,
            'message'      => $this->message ?? null,

            // fix operator precedence bug:
            // asset('storage/' . $this->file ?? null) => wrong (concats before ??)
            'file'         => $this->file ? asset('storage/' . $this->file) : null,

            'duration'     => $this->duration ?? null,
            'order_id'     => $this->order_id ?? null,
            'is_forwarded' => (bool) ($this->is_forwarded ?? false),
            'original_id'  => $this->original_id ?? null,
            'created_at'   => $this->created_at ?? null,
            'updated_at'   => $this->updated_at ?? null,
            'form_id'      => $this->form_id ?? null,
            'is_read'=>$this->is_read ?? null,

            'sender'   => new UserMiniResource($this->whenLoaded('sender')),
            'receiver' => new UserMiniResource($this->whenLoaded('receiver')),

            // ⚠️ Be careful with recursive depth on self()
            'original_message' => new self($this->whenLoaded('originalMessage')),

            'reply_to_id'   => $this->reply_to_id,
            'reply_preview' => $this->reply_preview,
            'is_deleted'=>$this->is_deleted,
            'is_edited'=>$this->is_edited,

            'reply_to' => $this->whenLoaded('replyTo', fn () => [
                'id'      => $this->replyTo->id,
                'type'    => $this->replyTo->type,
                'message' => $this->replyTo->message,
            ]),

            // NEW: payment_order (wrap in your OrderResource if you have one)
            'payment_order' => $paymentOrder        ];
    }
}
