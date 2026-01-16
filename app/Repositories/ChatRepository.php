<?php

namespace App\Repositories;

use App\Models\Chat;
use Illuminate\Support\Facades\Log;

class ChatRepository
{
    public function createChat(array $data)
    {
        return Chat::create($data);
        // Logic to create a chat
    }
    public function getChatById(int $id)
    {
        return Chat::with('messages', 'participantA', 'participantB', 'agent')->find($id);
    }
    public function updateChat(int $id, array $data)
    {
        $chat = Chat::find($id);
        if ($chat) {
            $chat->update($data);
            return $chat;
        }
        return null;
    }
    // public function 
    public function getChatByUserId(int $userId, ?string $role = null)
    {
        return Chat::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('user_2_id', $userId);
            })
            ->where('is_deleted_by_user', 0)
            ->with([
                'messages',
                'participantA',
                'participantB',
                'agent',
                'order'
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($userId) {
                    $q->where('is_read', 0)
                      ->where('sender_id', '!=', $userId);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    

    public function getChatByUserIdSIngle(int $userId)
    {
        return Chat::where('user_id', $userId)->orWhere('user_2_id', $userId)->with('messages', 'participantA', 'participantB', 'agent')->orderBy('created_at', 'desc')->first();
    }
    public function sendMessage(int $chatId, array $messageData)
    {
        $chat = $this->getChatById($chatId);
        if ($chat) {
            return $chat->messages()->create($messageData);
        }
        throw new \Exception('Chat not found');
    }
}
