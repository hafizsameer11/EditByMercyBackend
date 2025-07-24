<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ChatDTO;
use App\DTOs\MessageDTO;
use App\DTOs\OrderDTO;
use App\Enums\UserRoles;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAgentRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\AssignedAgentViewModel;
use App\Http\Resources\MessageResource;
use App\Models\User;
use App\Services\ChatService;
use App\Services\OrderService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $userService,
        $chatService,
        $orderService;

    public function __construct(UserService $userService, ChatService $chatService, OrderService $orderService)
    {
        $this->userService = $userService;
        $this->chatService = $chatService;
        $this->orderService = $orderService;
    }
public function sendMessage(SendMessageRequest $sendMessageRequest)
{
    try {
        $chatId = $sendMessageRequest->get('chat_id');

        // Get chat participants
        $chat = \App\Models\Chat::findOrFail($chatId);

        // Determine receiver: if sender is user_id then receiver is user_2_id, and vice versa
        $receiverId = $chat->user_id === Auth::id()
            ? $chat->user_2_id
            : $chat->user_id;

        // Build DTO and attach receiver ID dynamically
        $messageDto = MessageDTO::fromRequest($sendMessageRequest)
            ->withReceiverId($receiverId);

        $message = $this->chatService->sendMessage($chatId, $messageDto);

        // Load necessary relationships
        $message->load('sender', 'receiver', 'originalMessage');

        return ResponseHelper::success(new MessageResource($message), 'Message sent successfully.', 201);
    } catch (\Exception $e) {
        Log::error('Error sending message: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'request_data' => $sendMessageRequest->all(),
        ]);

        return ResponseHelper::error('An error occurred while sending the message.', 500);
    }
}

    public function assignAgent(AssignAgentRequest $request)
    {
        try {
            $authUserId = Auth::id();
            $serviceType = $request->input('service_type');

            // 1. Get agent
            $agent = $this->userService->getSupportAgent();

            // 2. Check if chat exists between the two
            $chat = $this->chatService->findChatBetweenUsers($authUserId, $agent->id ?? null);

            // If chat exists
            if ($chat) {
                // Check if it has a pending order with the same service type
                $pendingOrder = $chat->order()
                    ->where('service_type', $serviceType)
                    ->where('status', 'pending')
                    ->first();

                if ($pendingOrder) {
                    // Return the existing chat with loaded relationships
                    $data = $chat->load('participantA', 'participantB', 'agent', 'messages', 'order');
                    return ResponseHelper::success(new AssignedAgentViewModel($data), 'Existing pending order chat found.', 200);
                }

                // Check if chat has successful order
                $successfulOrder = $chat->order()
                    ->where('service_type', $serviceType)
                    ->where('status', 'success')
                    ->first();

                if ($successfulOrder) {
                    // Create new order inside same chat
                    $newOrderDTO = new OrderDTO(
                        user_id: $authUserId,
                        agent_id: $agent->id ?? null,
                        service_type: $serviceType,
                        chat_id: $chat->id ?? null
                    );
                    $this->orderService->createOrder($newOrderDTO);

                    $data = $chat->load('participantA', 'participantB', 'agent', 'messages', 'order');
                    return ResponseHelper::success(new AssignedAgentViewModel($data), 'New order created in existing chat.', 201);
                }
            }

            // If no existing chat or suitable order, create new chat
            $chatDto = new ChatDTO(
                type: 'user-agent',
                user_id: $authUserId,
                user_2_id: $agent->id ?? null,
                agent_id: $agent->id ?? null
            );
            $chat = $this->chatService->createChat($chatDto);

            $orderDto = new OrderDTO(
                user_id: $authUserId,
                agent_id: $agent->id ?? null,
                service_type: $serviceType,
                chat_id: $chat->id ?? null
            );
            $this->orderService->createOrder($orderDto);

            $data = $chat->load('participantA', 'participantB', 'agent', 'messages', 'order');
            return ResponseHelper::success(new AssignedAgentViewModel($data), 'New chat and order created.', 201);
        } catch (\Exception $e) {
            Log::error('Error assigning agent: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return ResponseHelper::error('An error occurred while assigning the agent.', 500);
        }
    }
}
