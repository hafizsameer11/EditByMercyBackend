<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLike extends Model
{
    //
    protected $fillable = ['feed_id', 'user_id'];

    public function feed() {
        return $this->belongsTo(Feed::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
