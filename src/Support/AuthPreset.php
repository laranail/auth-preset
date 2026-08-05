<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Support;

use LogicException;
use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;

class AuthPreset
{
    public static function stack(): FrontendStack
    {
        return FrontendStack::from(value: config(key: 'auth-preset.stack', default: 'blade'));
    }

    public static function guard(): string
    {
        return config(key: 'auth-preset.guard', default: 'web');
    }

    public static function webPrefix(): string
    {
        return config(key: 'auth-preset.prefix.web', default: 'auth');
    }

    public static function apiPrefix(): string
    {
        return config(key: 'auth-preset.prefix.api', default: 'api/auth');
    }

    public static function webMiddleware(): array
    {
        return config(key: 'auth-preset.middleware.web', default: ['web', 'guest']);
    }

    public static function apiMiddleware(): array
    {
        return config(key: 'auth-preset.middleware.api', default: ['api', 'guest', 'throttle:60,1']);
    }

    public static function afterLoginRedirect(): string
    {
        return config(key: 'auth-preset.redirects.after_login', default: '/dashboard');
    }

    public static function afterRegistrationRedirect(): string
    {
        return config(key: 'auth-preset.redirects.after_registration', default: '/dashboard');
    }

    public static function view(string $page): string
    {
        return match (self::stack()) {
            FrontendStack::Blade => 'auth-preset::blade.' . $page,
            default              => throw new LogicException('The configured auth-preset stack is not installed.'),
        };
    }
}
