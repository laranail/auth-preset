<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Support;

use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;

class AuthPreset
{
    public static function stack(): FrontendStack
    {
        return FrontendStack::from(value: config(key: 'auth-preset.stack', default: 'blade'));
    }

    public static function isInertia(): bool
    {
        return in_array(self::stack(), [
            FrontendStack::InertiaVue,
            FrontendStack::InertiaReact,
        ]);
    }

    public static function isInertiaVue(): bool
    {
        return self::stack() === FrontendStack::InertiaVue;
    }

    public static function isInertiaReact(): bool
    {
        return self::stack() === FrontendStack::InertiaReact;
    }

    public static function enabled(string $feature): bool
    {
        return (bool) config(key: "auth-preset.features.{$feature}", default: false);
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
}
