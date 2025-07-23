<?php

namespace App\Services;

use App\DTOs\ChatDTO;
use App\Repositories\ChatRepository;
use Exception;

class ChatService
{
    protected $chatRepository;
    public function __construct(ChatRepository $chatRepository){
        $this->chatRepository = $chatRepository;
    }

    public function createChat(ChatDTO $chatDTO)
    {
        try{
            $data = $chatDTO->toArray();
            $chat = $this->chatRepository->createChat($data);
            return $chat;
            
        }catch(Exception $e){
            throw $e;
        }
        
        // Logic to create a chat
    }
}
