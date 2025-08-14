<?php

namespace App\Services;

use App\DTOs\Auth\ChangePasswordDTO;
use App\DTOs\Auth\ForgetPasswordDTO;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\VerifyCodeDTO;
use App\Enums\UserRoles;
use App\Mail\ForgotPasswordOtpMail;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    public function register(RegisterDTO $registerDTO): User
    {
        try {
            $user = $this->userRepo->createUser($registerDTO->toArray());
            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepo->findByEmail($dto->getEmail());

        // 1️⃣ User not found
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email.'],
            ]);
        }

        // 2️⃣ Password mismatch
        if (!Hash::check($dto->getPassword(), $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'id' => $user->id
        ];
    }
    public function forgetPassword(ForgetPasswordDTO $forgetPasswordDTO)
    {
        try {
            $user = $this->userRepo->findByEmail($forgetPasswordDTO->getEmail());
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['No account found with this email.'],
                ]);
            }
            $otp = rand(1000, 9999);
            $UpdatedUser = $this->userRepo->update([
                'otp' => $otp
            ], $user->id);
            if (!$UpdatedUser) {
                throw new Exception('Something went wrong');
            }
            Mail::to($user->email)->send(new ForgotPasswordOtpMail($otp));
            return $user;
            // ])
        } catch (Exception $e) {
            return $e;
            // return ResponseHelper::error($e->getMessage());
        }
    }
    public function verifyCode(VerifyCodeDTO $verifyCodeDTO): Exception|User
    {
        try {
            $user = $this->userRepo->findByEmail($verifyCodeDTO->getEmail());
            if (!$user) {
                throw new Exception('No account found with this email.');
            }
            if ($user->otp != $verifyCodeDTO->getCode()) {
                throw new Exception('Invalid OTP');
            }
            $user->otp = null;
            $user->save();
            return $user;
        } catch (Exception $e) {
            return $e;
        }
    }
    public function changePassword(ChangePasswordDTO $changePasswordDTO)
    {
        try {
            $user = $this->userRepo->findByEmail($changePasswordDTO->getEmail());
            if (!$user) {
                throw new Exception('No account found with this email.');
            }
            $newPassword = Hash::make($changePasswordDTO->getPassword());
            // $user->save();
            $updatedUser = $this->userRepo->update([
                'password' => $newPassword
            ], $user->id);


            return $updatedUser;
        } catch (Exception $e) {
            return $e;
        }
    }
    public function getSupportAgent()
    {
        try {
            $agent = $this->userRepo->getUserByRole(UserRoles::SUPPORT)->first();
            if (!$agent) {
                throw new Exception('No support agent available');
            }
            return $agent;
        } catch (Exception $e) {
            return $e;
        }
    }
    public function getAllWithNonUserRoles()
    {
        return User::where('role', '!=', 'user')->get();
    }

   public function editProfile($data)
{
    try {
        $userId = Auth::id();
        $user = User::findOrFail($userId);

        // If profile_picture is uploaded
        if (isset($data['profile_picture']) && $data['profile_picture'] instanceof \Illuminate\Http\UploadedFile) {
            // Store in storage/app/public/profile_picture
            $path = $data['profile_picture']->store('profile_picture', 'public');

            // Replace old picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Save new path
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return $user;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
}
