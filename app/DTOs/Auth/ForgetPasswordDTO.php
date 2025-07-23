<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class ForgetPasswordDTO
{
    public function __construct(
        public string $email
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->input('email'),
        );
    }
    public function toArray(){
        return [
            'email' => $this->email,
            ];
    }
    public function getEmail(){
        return $this->email;
    }
}
