<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MessageDTO
{
    public function __construct(
        public ?int $chat_id = null,
        public int $sender_id,
        public ?int $receiver_id = null,
        public string $type = 'text',
        public ?string $message = null,
        public ?string $file = null,
        public ?int $duration = null,
        public ?int $order_id = null,
        public bool  $is_forwarded = false,
        public ?int $original_id = null,

        // NEW
        public ?int $reply_to_id = null,
        public ?string $reply_preview = null,
    ) {}

    /**
     * Generate a one-line preview for replies.
     */
    public static function generatePreviewFromParent(?\App\Models\Message $parent): ?string
    {
        if (!$parent) return null;

        return match ($parent->type) {
            'text'  => (string) Str::of((string) ($parent->message ?? ''))->squish()->limit(80, '…'),
            'image' => '📷 Photo',
            'video' => '🎥 Video',
            'voice' => '🎤 Voice message',
            'file'  => '📄 File',
            'order' => '🧾 Order',
            default => 'Replied message',
        };
    }

    public static function fromRequest(Request $request): self
    {
        $storedPath = null;

        Log::info('Creating MessageDTO from request', [
            'user_id'      => Auth::id(),
            'request_data' => $request->except(['file']), // avoid huge logs
        ]);

        // File upload (kept same behavior)
        if ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('chat_images', 'public');
            Log::info('File upload stored', [
                'user_id' => Auth::id(),
                'path'    => $storedPath,
                'name'    => $request->file('file')->getClientOriginalName(),
            ]);
        }

        // Reply fields
        $replyToId     = $request->integer('reply_to_id') ?: null;
        $replyPreview  = $request->filled('reply_preview') ? $request->string('reply_preview')->toString() : null;

        // If preview missing, compute here (controller will still validate chat)
        if ($replyToId && !$replyPreview) {
            $parent = \App\Models\Message::query()
                ->select(['id', 'type', 'message', 'chat_id'])
                ->find($replyToId);

            $replyPreview = self::generatePreviewFromParent($parent);
        }

        return new self(
            chat_id:      $request->get('chat_id'),
            sender_id:    Auth::id(),
            receiver_id:  $request->get('receiver_id'),
            type:         $request->get('type', 'text'),
            message:      $request->get('message'),
            file:         $storedPath,
            duration:     $request->get('duration'),
            order_id:     $request->get('order_id'),
            is_forwarded: $request->boolean('is_forwarded', false),
            original_id:  $request->get('original_id'),

            reply_to_id:   $replyToId,
            reply_preview: $replyPreview,
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
            original_id: null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'chat_id'      => $this->chat_id,
            'sender_id'    => $this->sender_id,
            'receiver_id'  => $this->receiver_id,
            'type'         => $this->type,
            'message'      => $this->message,
            'file'         => $this->file,
            'duration'     => $this->duration,
            'order_id'     => $this->order_id,
            'is_forwarded' => $this->is_forwarded,
            'original_id'  => $this->original_id,

            // NEW
            'reply_to_id'   => $this->reply_to_id,
            'reply_preview' => $this->reply_preview,
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
