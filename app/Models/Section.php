<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['title','form_id'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    public function form()
    {
        $this->belongsTo(Form::class, 'form_id');
    }
}
