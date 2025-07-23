<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class ChangePasswordDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->input('email'),
            password: $request->input('password'),
        );
    }
    public function toArray(){
        return [
            'email' => $this->email,
            'password' => $this->password,
            ];
    }
    public function getEmail(){
        return $this->email;
    }
    public function getPassword(){
        return $this->password;
    }
}
