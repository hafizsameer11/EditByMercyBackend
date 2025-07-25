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
    public function getChatByUserId(int $userId)
    {
        try {
            return $this->chatRepository->getChatByUserId($userId);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createChatIfNotExists($user, $otherUser)
{
    $existingChat = Chat::where(function ($query) use ($user, $otherUser) {
        $query->where('user_id', $user->id)
              ->where('user_2_id', $otherUser->id);
    })->orWhere(function ($query) use ($user, $otherUser) {
        $query->where('user_id', $otherUser->id)
              ->where('user_2_id', $user->id);
    })->first();

    if (!$existingChat) {
        return Chat::create([
            'user_id' => $user->id,
            'user_2_id' => $otherUser->id,
            'agent_id' => null,
            'type' => 'agent-agent',
        ]);
    }

    return $existingChat;
}
}
