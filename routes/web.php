<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\LoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\LogoutController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\RegisterController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\SocialCallbackController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\SocialRedirectController;

Route::prefix(AuthPreset::webPrefix())
    ->middleware([...AuthPreset::webMiddleware(), 'guest:' . AuthPreset::guard()])
    ->group(function (): void {
        if (Features::enabled(Features::registration())) {
            Route::get('/register', [RegisterController::class, 'create'])->name('register');
            Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
        }

        if (Features::enabled(Features::login())) {
            Route::get('/login', [LoginController::class, 'create'])->name('login');
            Route::post('/login', [LoginController::class, 'store'])->name('login.store');
        }

        if (Features::enabled(Features::social())) {
            Route::get('/social/{provider}', SocialRedirectController::class)->name('social.redirect');
            Route::get('/social/{provider}/callback', SocialCallbackController::class)->name('social.callback');
        }
    });

if (Features::enabled(Features::logout())) {
    Route::prefix(AuthPreset::webPrefix())
        ->middleware([...AuthPreset::webMiddleware(), 'auth:' . AuthPreset::guard()])
        ->group(function (): void {
            Route::post('/logout', [LogoutController::class, '__invoke'])->name('logout');
        });
}
