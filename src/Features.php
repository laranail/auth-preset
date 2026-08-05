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

    public static function api(): string
    {
        return 'api';
    }
}
