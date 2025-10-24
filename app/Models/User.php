<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protecte÷
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
        'is_blocked',
        'otp',
        'profile_picture',
        'phone',
        'oauth_provider',
        'oauth_id',
        'fcmToken',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function getProfilePictureAttribute($value)
    {
        if (!$value) {
            return null; // or return default image url
        }

        return asset('storage/'.$value);
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Get the chats where user is participant.
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id')
            ->orWhere('user_2_id', $this->id);
    }

    /**
     * Check if user is online based on last activity.
     * User is considered online if they were active within the last 5 minutes.
     */
    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        
        // User is online if last seen within 5 minutes
        return $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Get human-readable last seen time.
     */
    public function getLastSeenAttribute()
    {
        if (!$this->last_seen_at) {
            return 'Never';
        }

        if ($this->isOnline()) {
            return 'Online';
        }

        return $this->last_seen_at->diffForHumans();
    }
}
