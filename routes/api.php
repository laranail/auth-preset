<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\LoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\LogoutController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\RegisterController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\SocialCallbackController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\SocialRedirectController;

if (Features::enabled(Features::api())) {
    Route::prefix(AuthPreset::apiPrefix())
        ->middleware([...AuthPreset::apiMiddleware(), 'guest:' . AuthPreset::guard()])
        ->group(function (): void {
            if (Features::enabled(Features::registration())) {
                Route::post('/register', [RegisterController::class, 'store'])->name('api.register');
            }

            if (Features::enabled(Features::login())) {
                Route::post('/login', [LoginController::class, 'store'])->name('api.login');
            }

            if (Features::enabled(Features::social())) {
                Route::get('/social/{provider}', [SocialRedirectController::class, '__invoke'])->name('api.social.redirect');
                Route::get('/social/{provider}/callback', [SocialCallbackController::class, '__invoke'])->name('api.social.callback');
            }
        });

    if (Features::enabled(Features::logout())) {
        Route::prefix(AuthPreset::apiPrefix())
            ->middleware([...AuthPreset::apiMiddleware(), 'auth:' . AuthPreset::guard()])
            ->group(function (): void {
                Route::post('/logout', [LogoutController::class, '__invoke'])->name('api.logout');
            });
    }
}
