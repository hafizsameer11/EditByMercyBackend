<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class VerifyCodeDTO
{
    public function __construct(
        public string $code,
        public string $email,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: $request->input('code'),
            email: $request->input('email'),
        );
    }
    public function toArray(){
        return [
            'code'=>$this->code
        ];
    }
    public function getCode(){
        return $this->code;
    }
    public function getEmail(){
        return $this->email;
    }
}
