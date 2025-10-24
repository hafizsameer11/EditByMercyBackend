<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
     protected $fillable = ['admin_id', 'category_id', 'caption', 'description', 'featured_image', 'link'];

    public function category() {
        return $this->belongsTo(FeedCategory::class);
    }

    public function likes() {
        return $this->hasMany(FeedLike::class);
    }

    public function isLikedBy($userId) {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
