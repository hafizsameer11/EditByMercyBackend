<?php 


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? null,
            'chat_id' => $this->chat_id ?? null,
            'sender_id' => $this->sender_id ?? null,
            'receiver_id' => $this->receiver_id ?? null,
            'type' => $this->type ?? null,
            'message' => $this->message ?? null,
            'file' => $this->file ? asset('storage/' . $this->file ?? null) : null,
            'duration' => $this->duration ?? null,
            'order_id' => $this->order_id ?? null,
            'is_forwarded' => $this->is_forwarded ?? null,
            'original_id' => $this->original_id ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'form_id'=>$this->form_id ?? null,

            'sender' => new UserMiniResource($this->whenLoaded('sender')),
            'receiver' => new UserMiniResource($this->whenLoaded('receiver')),
            'original_message' => new self($this->whenLoaded('originalMessage')),
        ];
    }
}
