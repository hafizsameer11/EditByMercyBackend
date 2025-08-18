<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Message extends Model
{
     protected $fillable = [
        'chat_id',
        'sender_id',
        'receiver_id',
        'type',
        'message',
        'file',
        'duration',
        'order_id',
        'is_forwarded',
        'original_id',
        'is_read',
        'form_id',
          'reply_to_id','reply_preview',

    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function originalMessage()
    {
        return $this->belongsTo(Message::class, 'original_id');
    }
    
    // Relations you already added:
    public function replyTo()
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'reply_to_id');
    }

    /**
     * Canonical generator for previews from a parent message.
     */
    public function generateReplyPreview(?self $parent = null): ?string
    {
        $parent = $parent ?: $this->replyTo;   // will use eager-loaded relation when present
        if (!$parent) return null;

        return match ($parent->type) {
            'text'  => (string) Str::of((string) ($parent->message ?? ''))
                            ->squish()->limit(80, '…'),
            'image' => '📷 Photo',
            'video' => '🎥 Video',
            'voice' => '🎤 Voice message',
            'file'  => '📄 File',
            'order' => '🧾 Order',
            default => 'Replied message',
        };
    }

    /**
     * Accessor that falls back to computed value if DB is null.
     * Laravel 9/10+ Attribute accessor.
     */
    protected function replyPreview(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value !== null) return $value;
                // compute on the fly using relation (avoid N+1 by eager loading in queries)
                return $this->generateReplyPreview();
            },
        );
    }
}
