<?php


namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MessageDTO
{
    public function __construct(
        public ?int $chat_id=null,
        public int $sender_id,
        public ?int $receiver_id = null,
        public string $type = 'text',
        public ?string $message = null,
        public ?string $file = null,
        public ?int $duration = null,
        public ?int $order_id = null,
        public bool $is_forwarded = false,
        public ?int $original_id = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $imagePath = null;
        Log::info('Creating MessageDTO from request', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);
        if ($request->hasFile('file')) {
            Log::info('File upload detected', [
                'user_id' => Auth::id(),
                'file_name' => $request->file('file')->getClientOriginalName(),
            ]);
            $imagePath = $request->file('file')->store('chat_images', 'public');
        }

        return new self(
            chat_id: $request->get('chat_id'),
            sender_id: Auth::id(),
            receiver_id: $request->get('receiver_id'),
            type: $request->get('type', 'text'),
            message: $request->get('message'),
            file: $imagePath,
            duration: $request->get('duration'),
            order_id: $request->get('order_id'),
            is_forwarded: $request->boolean('is_forwarded', false),
            original_id: $request->get('original_id'),
        );
    }

    public static function fromForwardedMessage(\App\Models\Message $original, int $chat_id, int $sender_id): self
    {
        return new self(
            chat_id: $chat_id,
            sender_id: $sender_id,
            receiver_id: null,
            type: $original->type ?? 'text',
            message: $original->message ?? null,
            file: $original->file ?? null,
            duration: $original->duration ?? null,
            order_id: $original->order_id ?? null,
            is_forwarded: true,
            original_id: $original->id ?? null
        );
    }

    public static function fromForwardedOrder(\App\Models\Order $order, int $chat_id, int $sender_id): self
    {
        return new self(
            chat_id: $chat_id,
            sender_id: $sender_id,
            receiver_id: null,
            type: 'order',
            message: null,
            file: null,
            duration: null,
            order_id: $order->id ?? null,
            is_forwarded: true,
            original_id: null // no original message here, just order
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'type' => $this->type,
            'message' => $this->message,
            'file' => $this->file,
            'duration' => $this->duration,
            'order_id' => $this->order_id,
            'is_forwarded' => $this->is_forwarded,
            'original_id' => $this->original_id,
        ], fn($v) => $v !== null);
    }
    public function withReceiverId(int $receiverId): self
    {
        $this->receiver_id = $receiverId;
        return $this;
    }
    public function withChatId(int $chatId): self
    {
        $this->chat_id = $chatId;
        return $this;
    }
}
