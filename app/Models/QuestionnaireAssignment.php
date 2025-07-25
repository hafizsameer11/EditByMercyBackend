<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionnaireAssignment extends Model
{
     protected $fillable = [
        'form_id',
        'chat_id',
        'user_id',
        'status',
        'completed_sections',
        'total_sections',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class); // optional
    }
}
