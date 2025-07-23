<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Auth\ChangePasswordDTO;
use App\DTOs\Auth\ForgetPasswordDTO;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\VerifyCodeDTO;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\CodeVerificationRequest;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use Exception;

class AuthController extends Controller
{
     public function __construct(protected UserService $userService) {}

     public function register(RegisterRequest $request)
     {
          try {
               $dto = RegisterDTO::fromRequest($request); // ✅ Pass the request object, not validated array
               $user = $this->userService->register($dto);

               return ResponseHelper::success($user);
          } catch (Exception $e) {
               return ResponseHelper::error($e->getMessage());
          }
     }
     public function login(LoginRequest $request)
     {
          try {
               $dto = LoginDTO::fromRequest($request);
               $user = $this->userService->login($dto);

               return ResponseHelper::success($user);
          } catch (Exception $e) {
               return ResponseHelper::error($e->getMessage());
          }
     }
     public function forgetPassword(ForgetPasswordRequest $request)
     {
          try {
               $dto = ForgetPasswordDTO::fromRequest($request);
               $this->userService->forgetPassword($dto);
               return ResponseHelper::success(null, 'Password reset code sent to your email.');
          } catch (Exception $e) {
               return ResponseHelper::error($e->getMessage());
          }
     }
     public function verifyCode(CodeVerificationRequest $request)
     {
          try {
               $dto = VerifyCodeDTO::fromRequest($request);
               $this->userService->verifyCode($dto);
               return ResponseHelper::success(null, 'Code verification successful. Now you can update your password');
          } catch (Exception $e) {
               return ResponseHelper::error($e->getMessage());
          }
     }
     public function changePassword(ChangePasswordRequest $request)
     {
          try {
               $dto = ChangePasswordDTO::fromRequest($request);
               $this->userService->changePassword($dto);
               return ResponseHelper::success(null, 'Password changed successfully');
          } catch (Exception $e) {
               return ResponseHelper::error($e->getMessage());
          }
     }
}
