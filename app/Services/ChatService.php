<?php

namespace App\Services;

use App\DTOs\ChatDTO;
use App\DTOs\MessageDTO;
use App\Models\Chat;
use App\Repositories\ChatRepository;
use Exception;

class ChatService
{
    protected $chatRepository;
    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function createChat(ChatDTO $chatDTO)
    {
        try {
            $data = $chatDTO->toArray();
            $chat = $this->chatRepository->createChat($data);
            return $chat;
        } catch (Exception $e) {
            throw $e;
        }

        // Logic to create a chat
    }
    public function findChatBetweenUsers($userId, $agentId)
    {
        try {
            return Chat::where(function ($query) use ($userId, $agentId) {
                $query->where('user_id', $userId)->where('user_2_id', $agentId);
            })->orWhere(function ($query) use ($userId, $agentId) {
                $query->where('user_id', $agentId)->where('user_2_id', $userId);
            })
                ->where('type', 'user-agent')
                ->first();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getChatById(int $id)
    {
        try {
            return $this->chatRepository->getChatById($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendMessage(int $chatId, MessageDTO $messageData)
    {
        try {
            return $this->chatRepository->sendMessage($chatId, $messageData->toArray());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
