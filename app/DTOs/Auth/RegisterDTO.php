<?php

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;

class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public ?string $profile_picture = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: bcrypt($request->input('password')),
            role: $request->input('role', 'user'),
            profile_picture: $request->hasFile('profile_picture')
                ? $request->file('profile_picture')->store('profiles', 'public')
                : null,
        );
    }
    public function toArray(): array
{
    return [
        'name' => $this->name,
        'email' => $this->email,
        'password' => $this->password,
        'role' => $this->role,
        'profile_picture' => $this->profile_picture,
    ];
}
}
