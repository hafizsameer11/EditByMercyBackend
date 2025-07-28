<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ChatDTO;
use App\DTOs\MessageDTO;
use App\DTOs\OrderDTO;
use App\Enums\UserRoles;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAgentRequest;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\AssignedAgentViewModel;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\Order;
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
    public function getChatMessages($chatId)
    {
        try {
            $chat = $this->chatService->getChatById($chatId);
            if (!$chat) {
                return ResponseHelper::error('Chat not found.', 404);
            }
            $messages = $chat->messages()->with(['sender', 'receiver', 'originalMessage'])->get();
            $order = Order::where('chat_id', '=', $chatId)->first();

            $userId = Auth::id();

            // ✅ Mark all unread messages for this user in this chat as read
            Message::where('chat_id', $chatId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
            return ResponseHelper::success([
                'order' => $order,
                'messages' => MessageResource::collection($messages),
            ], 'Chat messages fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching chat messages: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'chat_id' => $chatId,
            ]);
            return ResponseHelper::error('An error occurred while fetching chat messages.', 500);
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
    public function getChats()
    {
        try {
            $userId = Auth::id();
            $chats = $this->chatService->getChatByUserId($userId);
            return ResponseHelper::success(ChatResource::collection($chats), 'Chats fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching chats: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);
            return ResponseHelper::error('An error occurred while fetching chats.', 500);
        }
    }
    public function createPayment(CreatePaymentRequest $request)
    {
        try {
            // $dto = CreatePaymentDTO::fromRequest($request);
            $data = $request->validated();
            $payment = $this->orderService->createPayment($data);
            $message=Message::create([
                'sender_id' => Auth::id(),
                'chat_id' => $data['chat_id'],
                'message' => "Please Check this Order and make payment",
                "type"=>"payment"

            ]);
            // $payment = $this->paymentService->createPayment($dto);
            return ResponseHelper::success($payment, 'Payment created successfully.', 201);
        } catch (\Exception $e) {
            Log::error('Error creating payment: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);
            return ResponseHelper::error('An error occurred while creating the payment.', 500);
        }
    }
public function updatePayment(Request $request){
    $chatId=$request->chat_id;
    $order=Order::where('chat_id',$chatId)->orderBy('created_at','desc')->first();
    $order->payment_status='success';
    $order->save();
    return ResponseHelper::success($order, 'Order updated successfully.');
}
public function updateOrderStatus(Request $request){
    $chatId=$request->chat_id;
    $order=Order::where('chat_id',$chatId)->orderBy('created_at','desc')->first();
    $order->status=$request->status;
    $order->save();
    return ResponseHelper::success($order, 'Order updated successfully.');
}

    //for agents to get other agents
    public function getNonUsers()
    {
        try {
            $userId = Auth::id();
            $users = User::whereNot('role', 'user')
                ->whereNot('id', $userId)
                ->get();
            return ResponseHelper::success($users, 'Non-users fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching non-users: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);
            return ResponseHelper::error('An error occurred while fetching non-users.', 500);
        }
    }
    public function getChatWithUserByUserId($userId)
    {
        try {

            $chats = $this->chatService->getChatByUserId($userId);
            return ResponseHelper::success(ChatResource::collection($chats), 'Chats fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching chats: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);
            return ResponseHelper::error('An error occurred while fetching chats.', 500);
        }
    }
}
