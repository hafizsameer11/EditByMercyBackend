<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}
    public function createUser(RegisterRequest $request)
    {
        try {
            $dto = RegisterDTO::fromRequest($request);
            $user = $this->userService->register($dto);

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
