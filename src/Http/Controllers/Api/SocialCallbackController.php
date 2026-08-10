<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractSocialCallbackController;

class SocialCallbackController extends AbstractSocialCallbackController
{
    protected function guard(): string
    {
        return \Simtabi\Laranail\AuthPreset\Support\AuthPreset::guard();
    }

    protected function resolve(SocialProvider $provider): Closure
    {
        return function (SocialiteUser $socialUser) use ($provider): ?Authenticatable {
            $social = Social::query()
                ->where('provider', $provider->value)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($social !== null) {
                $social->update([
                    'token'         => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at'    => $socialUser->expiresIn
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]);

                return $social->socialable;
            }

            $guard = config('auth.defaults.guard');
            $authProvider = config("auth.guards.{$guard}.provider");
            $userModel = config('auth-kit.user_model')
                ?? config("auth.providers.{$authProvider}.model");

            $existingUser = null;
            if ($socialUser->getEmail() !== null) {
                $existingUser = $userModel::query()
                    ->where('email', $socialUser->getEmail())
                    ->first();
            }

            if ($existingUser !== null) {
                Social::query()->create([
                    'socialable_type' => get_class($existingUser),
                    'socialable_id'   => $existingUser->getAuthIdentifier(),
                    'provider'        => $provider->value,
                    'provider_id'     => $socialUser->getId(),
                    'name'            => $socialUser->getName(),
                    'nickname'        => $socialUser->getNickname(),
                    'email'           => $socialUser->getEmail(),
                    'avatar_path'     => $socialUser->getAvatar(),
                    'token'           => $socialUser->token,
                    'refresh_token'   => $socialUser->refreshToken,
                    'expires_at'      => $socialUser->expiresIn
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]);

                return $existingUser;
            }

            $user = new $userModel();
            $user->fill([
                'name'     => $socialUser->getName() ?? $socialUser->getNickname() ?? '',
                'email'    => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
            ]);
            $user->save();

            Social::query()->create([
                'socialable_type' => get_class($user),
                'socialable_id'   => $user->getAuthIdentifier(),
                'provider'        => $provider->value,
                'provider_id'     => $socialUser->getId(),
                'name'            => $socialUser->getName(),
                'nickname'        => $socialUser->getNickname(),
                'email'           => $socialUser->getEmail(),
                'avatar_path'     => $socialUser->getAvatar(),
                'token'           => $socialUser->token,
                'refresh_token'   => $socialUser->refreshToken,
                'expires_at'      => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
            ]);

            return $user;
        };
    }

    protected function passed(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json(data: [
            'status' => 'passed',
            'user'   => $result->user,
        ]);
    }

    protected function failed(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json(data: [
            'status'  => 'failed',
            'message' => 'Social authentication failed.',
        ], status: 422);
    }
}
