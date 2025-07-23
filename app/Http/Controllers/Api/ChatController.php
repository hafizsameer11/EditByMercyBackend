<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ChatDTO;
use App\DTOs\OrderDTO;
use App\Enums\UserRoles;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAgentRequest;
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

    public function assignAgent(AssignAgentRequest $request)
    {
        try {

            $agent = $this->userService->getSupportAgent();
            $chatDto = new ChatDTO(
                type: 'user-agent',
                user_id: Auth::id(),
                user_2_id: $agent->id,
                agent_id: $agent->id
            );

            $chat = $this->chatService->createChat($chatDto);
            $ordDTO = new OrderDTO(
                user_id: Auth::id(),
                agent_id: $agent->id,
                service_type: $request->input('service_type'),
                chat_id: $chat->id
            );
            $order = $this->orderService->createOrder($ordDTO);
            // $chat->
            $data = $chat->load('participantA', 'participantB', 'agent', 'messages', 'order');
            return ResponseHelper::success(
                'Agent assigned successfully.',
                $data,
                201
            );
        } catch (\Exception $e) {
            Log::error('Error assigning agent: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);
            return ResponseHelper::error(
                'An error occurred while assigning the agent.',
                500,

            );
            //   return response()->json(['error' => 'An error occurred while assigning the agent.'], 500);
        }
    }
}
