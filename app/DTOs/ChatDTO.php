<?php 
// app/DTOs/ChatDTO.php

namespace App\DTOs;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class ChatDTO
{
    public function __construct(
        public string $type,
        public ?int $user_id = null,
        public ?int $user_2_id = null,
        public ?int $agent_id = null
    ) {}

public static function fromRequest(Request $request): self
{
    return new self(
        type: $request->get('type', 'user-agent'),
        user_id: FacadesAuth::id(),
        user_2_id: $request->has('user_2_id') ? $request->get('user_2_id') : null,
        agent_id: $request->has('agent_id') ? $request->get('agent_id') : null,
    );
}


    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'user_id' => $this->user_id,
            'user_2_id' => $this->user_2_id,
            'agent_id' => $this->agent_id,
        ], fn ($value) => $value !== null);
    }
    public function withAgentId(int $agentId): self
    {
        $this->agent_id = $agentId;
        return $this;
    }
    public function withUserId(int $userId): self
    {
        $this->user_id = $userId;
        return $this;
    }
    public function withUser2Id(int $user2Id): self
    {
        $this->user_2_id = $user2Id;
        return $this;
    }
}
