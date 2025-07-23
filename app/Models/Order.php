<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'status',
        'total_amount',
        'payment_method',
        'no_of_photos',
        'delivery_date',
        'service_type',
        'payment_status',
        'txn',
        'chat_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
