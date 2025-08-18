<?php

namespace App\Repositories;

use App\Models\Chat;

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
  public function getChatByUserId(int $userId)
{
    return Chat::where('user_id', $userId)
        ->orWhere('user_2_id', $userId)
        ->with(['messages', 'participantA', 'participantB', 'agent', 'order'])
        ->withCount([
            'messages as unread_count' => function ($query) use ($userId) {
                $query->where('is_read', false)
                      ->where('sender_id', '!=', $userId); // exclude user's own messages
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
