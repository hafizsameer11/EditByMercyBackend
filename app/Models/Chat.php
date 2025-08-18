<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'user_2_id',
        'agent_id',
    ];

    // public function messages(): HasMany
    // {
    //     return $this->hasMany(Message::class);
    // }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function messagesAll()
{
    return $this->hasMany(Message::class);
}

    public function participantA()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participantB()
    {
        return $this->belongsTo(User::class, 'user_2_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    public function scopeUserChats($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->orWhere('user_2_id', $userId);
    }
    public function scopeAgentChats($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
    public function scopeUserType($query, $userId, $type)
    {
        return $query->where(function ($q) use ($userId, $type) {
            $q->where('user_id', $userId)
                ->orWhere('user_2_id', $userId);
        })->where('type', $type);
    }
    public function order()
    {
        return $this->hasOne(Order::class, 'chat_id');
    }
}
