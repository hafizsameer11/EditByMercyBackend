<?php 


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthController extends Controller
{
    public function loginWithToken(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return response()->json(['status' => false, 'message' => 'Invalid provider'], 400);
        }

        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($provider);

            // Optional; safe to skip for token flow
            if (method_exists($driver, 'stateless')) {
                $driver = $driver->stateless();
            }

            $socialUser = $driver->userFromToken($request->access_token);

            if (!$socialUser->getEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No email returned by '.$provider.'. Ensure email scope/permission.',
                ], 422);
            }

            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name'               => $socialUser->getName() ?: ($socialUser->getNickname() ?: 'User'),
                    'profile_picture'    => $socialUser->getAvatar(),
                    'oauth_provider'     => $provider,
                    'oauth_id'           => (string) $socialUser->getId(),
                    'email_verified_at'  => now(),
                    'password'           => bcrypt(Str::random(32)),
                ]
            );

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'status' => true,
                'token'  => $token,
                'user'   => $user,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Social login failed',
                'error'   => app()->environment('local') ? $e->getMessage() : null,
            ], 422);
        }
    }
}
