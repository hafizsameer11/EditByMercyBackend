<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    //
      protected $fillable = ['section_id', 'text', 'type', 'order'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function responses()
    {
        return $this->hasMany(QuestionResponse::class);
    }
}
