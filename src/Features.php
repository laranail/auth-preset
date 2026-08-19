<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset;

use Simtabi\Laranail\AuthPreset\Enums\AuthenticationFeature;

final class Features
{
    public static function enabled(string $feature): bool
    {
        return in_array(needle: $feature, haystack: config(key: 'auth-preset.features', default: []), strict: true);
    }

    public static function login(): string
    {
        return AuthenticationFeature::LOGIN->value;
    }

    public static function registration(): string
    {
        return AuthenticationFeature::REGISTRATION->value;
    }

    public static function logout(): string
    {
        return AuthenticationFeature::LOGOUT->value;
    }

    public static function social(): string
    {
        return AuthenticationFeature::SOCIAL->value;
    }

    public static function api(): string
    {
        return AuthenticationFeature::API->value;
    }

    public static function passwordReset(): string
    {
        return AuthenticationFeature::PASSWORD_RESET->value;
    }

    public static function updateProfileInformation(): string
    {
        return AuthenticationFeature::UPDATE_PROFILE_INFORMATION->value;
    }

    public static function updatePasswords(): string
    {
        return AuthenticationFeature::UPDATE_PASSWORDS->value;
    }

    public static function emailVerification(): string
    {
        return AuthenticationFeature::EMAIL_VERIFICATION->value;
    }

    public static function passkeys(): string
    {
        return AuthenticationFeature::PASSKEYS->value;
    }

    public static function botProtection(): string
    {
        return AuthenticationFeature::BOT_PROTECTION->value;
    }
}
