<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
     protected $fillable = [
        'order_id',
        'amount',
        'status',
        'service_type',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
