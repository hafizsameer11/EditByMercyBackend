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
        return Chat::where('user_id', $userId)->with('messages', 'participantA', 'participantB', 'agent')->get();
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
