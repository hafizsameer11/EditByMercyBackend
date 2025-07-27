<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'form_id'
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
    
}
