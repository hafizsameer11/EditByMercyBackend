<?php

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
 

      public function toArray($request)
    {
        $currentUserId = Auth::id();
        $isUserA = $this->user_id === $currentUserId;

        // Resolve unread_count with best available source.
        $unreadCount = null;

        // 1) Prefer withCount('messages as unread_count' ...) if present
        if (isset($this->unread_count)) {
            $unreadCount = (int) $this->unread_count;
        }
        // 2) If messages relation is loaded, count on the collection (no extra query)
        elseif ($this->relationLoaded('messages')) {
            $unreadCount = $this->messages
                ->where('is_read', false)
                ->where('sender_id', '!=', $currentUserId)
                ->count();
        }
        // 3) Fallback: run a tiny count query
        else {
            $unreadCount = $this->messages()
                ->where('is_read', false)
                ->where('sender_id', '!=', $currentUserId)
                ->count();
        }

        return [
            'id'            => $this->id ?? null,
            'type'          => $this->type ?? 'chat',
            'user_id'       => $this->user_id ?? null,
            'user_2_id'     => $this->user_2_id ?? null,
            'agent_id'      => $this->agent_id ?? null,
            'created_at'    => $this->created_at ?? null,
            'updated_at'    => $this->updated_at ?? null,
            'category'      => $this->order->service_type ?? null,
            'status'        => $this->order->status ?? null,

            'messages'      => MessageResource::collection($this->whenLoaded('messages')),
            'participant_a' => $isUserA
                ? new UserMiniResource($this->whenLoaded('participantA'))
                : new UserMiniResource($this->whenLoaded('participantB')),
            'participant_b' => $isUserA
                ? new UserMiniResource($this->whenLoaded('participantB'))
                : new UserMiniResource($this->whenLoaded('participantA')),
            'agent'         => new UserMiniResource($this->whenLoaded('agent')),

            // ✅ expose it here
            'unread_count'  => Message::where('chat_id', $this->id)->where('is_read', false)->where('sender_id', '!=', $currentUserId)->count(),
        ];
    }
}
