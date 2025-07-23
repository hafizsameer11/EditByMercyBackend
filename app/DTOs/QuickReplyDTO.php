<?php

namespace App\DTOs;

// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickReplyDTO
{
    public function __construct(
        public string $text,
        public int $user_id
    ) {}

    public static function fromRequest(Request $request): self
    {
        $user=Auth::user(); // Assuming you are using Laravel's Auth facade to get the authenticated user
        return new self(
            text: $request->input('text'),
            user_id: $user->id??0 // Assuming the user is authenticated
        );
    }
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'user_id' => $this->user_id,
        ];
    }
    public function getText():string{
        return $this->text; 
    }
    public function getUser(){
        return $this->user_id;
    }
}
