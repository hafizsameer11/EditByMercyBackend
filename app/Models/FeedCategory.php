<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedCategory extends Model
{
    protected $fillable = ['name'];

    public function feeds()
    {
        return $this->hasMany(Feed::class, 'category_id');
    }
}
