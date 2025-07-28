<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatQuestionnaireAnswer extends Model
{
     protected $fillable = ['chat_id', 'user_id', 'answers'];

    protected $casts = [
        'answers' => 'array',
    ];
}
