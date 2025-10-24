<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'answers',
        'completed_sections',
        'progress'
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_sections' => 'integer',
        'progress' => 'integer'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

