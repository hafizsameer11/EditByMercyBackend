<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatsController extends Controller
{
    /**
     * Get all chats with filtering
     */
    public function index(Request $request)
    {
        try {
            // Calculate stats
            $totalChats = Chat::count();
            $activeChats = Chat::whereHas('messages', function ($q) {
                $q->where('created_at', '>=', now()->subHours(24));
            })->count();
            $chatsWithOrders = Chat::whereHas('order')->count();

            $query = Chat::with([
                'participantA:id,name,profile_picture',
                'participantB:id,name,profile_picture',
                'agent:id,name,profile_picture',
                'order:id,chat_id,service_type,total_amount,status,no_of_photos',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
                ->withCount([
                    'messages as unread_count' => function ($query) {
                        $query->where('is_read', 0);
                    }
                ]);

            // Filter by service type
            if ($request->has('service_type') && $request->service_type && $request->service_type !== 'All') {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('service_type', $request->service_type);
                });
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('participantA', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('participantB', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('agent', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
                });
            }

            // Filter by type (Activity/Chats/Orders tabs)
            $type = $request->get('tab', 'chats');
            if ($type === 'chats') {
                // Show all chats
            } elseif ($type === 'activity') {
                // Show chats with recent activity
                $query->whereHas('messages', function ($q) {
                    $q->where('created_at', '>=', now()->subHours(24));
                });
            } elseif ($type === 'orders') {
                // Show chats with orders
                $query->whereHas('order');
            }

            $perPage = $request->get('per_page', 20);
            $chats = $query->orderBy('updated_at', 'desc')
                ->paginate($perPage);

            $transformedChats = $chats->getCollection()->map(function ($chat) {
                $lastMessage = $chat->messages->first();
                
                return [
                    'id' => $chat->id,
                    'agent_name' => $chat->participantA->name ?? 'N/A',
                    'agent_profile' => $chat->participantA->profile_picture ?? null,
                    'service' => $chat->order->service_type ?? 'N/A',
                    'order_amount' => $chat->order ? 'N' . number_format($chat->order->total_amount, 2) : 'N0.00',
                    'no_of_photos' => $chat->order->no_of_photos ?? 0,
                    'date' => $chat->created_at->format('m/d/y - h:i A'),
                    'status' => $chat->order->status ?? 'N/A',
                    'unread_count' => $chat->unread_count ?? 0,
                    'has_questionnaire' => $chat->messages()->where('type', 'questionnaire')->exists(),
                    'chat_id' => $chat->id,
                ];
            });

            return ResponseHelper::success([
                'stats' => [
                    'total_chats' => $totalChats,
                    'active_chats' => $activeChats,
                    'chats_with_orders' => $chatsWithOrders,
                ],
                'chats' => $transformedChats,
                'pagination' => [
                    'current_page' => $chats->currentPage(),
                    'last_page' => $chats->lastPage(),
                    'per_page' => $chats->perPage(),
                    'total' => $chats->total(),
                ]
            ], 'Chats fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch chats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single chat with messages
     */
    public function show($id)
    {
        try {
            $chat = Chat::with([
                'participantA:id,name,profile_picture,role',
                'participantB:id,name,profile_picture,role',
                'agent:id,name,profile_picture',
                'order',
                'messages' => function ($q) {
                    $q->with(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture'])
                        ->orderBy('created_at', 'asc');
                }
            ])->findOrFail($id);

            // Get order info if exists
            $orderInfo = null;
            if ($chat->order) {
                $orderInfo = [
                    'id' => $chat->order->id,
                    'customer_name' => $chat->participantA->name ?? 'N/A',
                    'service_type' => $chat->order->service_type,
                    'status' => $chat->order->status,
                    'payment_status' => $chat->order->payment_status,
                    'total_amount' => $chat->order->total_amount,
                    'no_of_photos' => $chat->order->no_of_photos,
                    'delivery_date' => $chat->order->delivery_date,
                ];
            }

            // Transform messages
            $messages = $chat->messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name ?? 'Unknown',
                    'sender_profile' => $msg->sender->profile_picture ?? null,
                    'receiver_id' => $msg->receiver_id,
                    'type' => $msg->type,
                    'message' => $msg->message,
                    'file' => $msg->file ? asset('storage/' . $msg->file) : null,
                    'duration' => $msg->duration,
                    'is_read' => $msg->is_read,
                    'created_at' => $msg->created_at->format('h:i A'),
                    'date' => $msg->created_at->format('M d, Y'),
                ];
            });

            $data = [
                'id' => $chat->id,
                'type' => $chat->type,
                'participant_a' => [
                    'id' => $chat->participantA->id ?? null,
                    'name' => $chat->participantA->name ?? 'N/A',
                    'profile_picture' => $chat->participantA->profile_picture ?? null,
                    'role' => $chat->participantA->role ?? 'user',
                ],
                'participant_b' => [
                    'id' => $chat->participantB->id ?? null,
                    'name' => $chat->participantB->name ?? 'N/A',
                    'profile_picture' => $chat->participantB->profile_picture ?? null,
                    'role' => $chat->participantB->role ?? 'agent',
                ],
                'order' => $orderInfo,
                'messages' => $messages,
                'created_at' => $chat->created_at,
            ];

            return ResponseHelper::success($data, 'Chat details fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Chat not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Create new order in existing chat
     */
    public function createNewOrder(Request $request, $chatId)
    {
        try {
            $chat = Chat::findOrFail($chatId);

            $validator = Validator::make($request->all(), [
                'service_type' => 'required|string',
                'customer_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // Get or verify customer
            $customer = User::where('name', $request->customer_name)
                ->orWhere('id', $chat->user_id)
                ->first();

            if (!$customer) {
                return ResponseHelper::error('Customer not found', 404);
            }

            // Create new order
            $order = Order::create([
                'user_id' => $customer->id,
                'agent_id' => $chat->agent_id,
                'chat_id' => $chatId,
                'service_type' => $request->service_type,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => 0,
            ]);

            // Create message in chat about new order
            Message::create([
                'chat_id' => $chatId,
                'sender_id' => Auth::id(),
                'receiver_id' => $customer->id,
                'type' => 'order',
                'message' => 'New order created: ' . $request->service_type,
                'order_id' => $order->id,
            ]);

            return ResponseHelper::success([
                'order' => $order,
                'message' => 'Order created and added to chat'
            ], 'New order created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get available chats for sharing/forwarding
     */
    public function getAvailableChats(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $chats = Chat::with([
                'participantA:id,name,profile_picture',
                'participantB:id,name,profile_picture',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
                ->where(function ($q) use ($search) {
                    if ($search) {
                        $q->whereHas('participantA', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        })->orWhereHas('participantB', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                    }
                })
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get();

            $transformedChats = $chats->map(function ($chat) {
                $lastMessage = $chat->messages->first();
                
                return [
                    'id' => $chat->id,
                    'name' => $chat->participantA->name ?? 'N/A',
                    'profile_picture' => $chat->participantA->profile_picture ?? null,
                    'last_chat' => $lastMessage ? 'Last chat with ' . ($lastMessage->sender->name ?? 'Unknown') : 'No messages',
                    'is_online' => !empty($chat->participantA->fcmToken ?? false),
                ];
            });

            return ResponseHelper::success($transformedChats, 'Available chats fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch chats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Share/Forward to another chat
     */
    public function shareToChat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_chat_id' => 'required|exists:chats,id',
                'to_chat_id' => 'required|exists:chats,id',
                'message_id' => 'nullable|exists:messages,id',
                'content' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $toChat = Chat::findOrFail($request->to_chat_id);
            
            // Determine receiver
            $receiverId = $toChat->user_id !== Auth::id() ? $toChat->user_id : $toChat->user_2_id;

            if ($request->has('message_id')) {
                // Forward existing message
                $originalMessage = Message::findOrFail($request->message_id);
                
                $forwardedMessage = Message::create([
                    'chat_id' => $request->to_chat_id,
                    'sender_id' => Auth::id(),
                    'receiver_id' => $receiverId,
                    'type' => $originalMessage->type,
                    'message' => $originalMessage->message,
                    'file' => $originalMessage->file,
                    'is_forwarded' => true,
                    'original_id' => $originalMessage->id,
                ]);
            } else {
                // Send new message
                $forwardedMessage = Message::create([
                    'chat_id' => $request->to_chat_id,
                    'sender_id' => Auth::id(),
                    'receiver_id' => $receiverId,
                    'type' => 'text',
                    'message' => $request->input('content', 'Shared content'),
                ]);
            }

            return ResponseHelper::success([
                'message' => $forwardedMessage,
                'chat_id' => $request->to_chat_id
            ], 'Shared successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to share: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete chat (soft delete)
     */
    public function destroy($id)
    {
        try {
            $chat = Chat::findOrFail($id);
            $chat->is_deleted_by_admin = true;
            $chat->save();

            return ResponseHelper::success(null, 'Chat deleted successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete chat: ' . $e->getMessage(), 500);
        }
    }
}

