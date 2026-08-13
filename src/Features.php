<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset;

final class Features
{
    public static function enabled(string $feature): bool
    {
        return in_array(needle: $feature, haystack: config(key: 'auth-preset.features', default: []), strict: true);
    }

    public static function login(): string
    {
        return 'login';
    }

    public static function registration(): string
    {
        return 'registration';
    }

    public static function logout(): string
    {
        return 'logout';
    }

    public static function social(): string
    {
        return 'social';
    }

    public static function api(): string
    {
        return 'api';
    }

    public static function passwordReset(): string
    {
        return 'password-reset';
    }

    public static function updateProfileInformation(): string
    {
        return 'update-profile-information';
    }

    public static function updatePasswords(): string
    {
        return 'update-passwords';
    }

    public static function emailVerification(): string
    {
        return 'email-verification';
    }
}
