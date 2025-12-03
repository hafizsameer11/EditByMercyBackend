<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_path',
        'recipient_type',
        'created_by',
    ];
}


