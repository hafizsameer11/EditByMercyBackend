<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
     protected $fillable = ['question_id', 'option_text', 'order'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
