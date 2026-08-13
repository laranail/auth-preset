<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

Route::prefix(AuthPreset::webPrefix())
    ->middleware([...AuthPreset::webMiddleware(), 'guest:' . AuthPreset::guard()])
    ->group(function (): void {
        if (Features::enabled(Features::registration())) {
            Route::get('/register', [Auth\RegisterController::class, 'create'])->name('register');
            Route::post('/register', [Auth\RegisterController::class, 'store'])->name('register.store');
        }

        if (Features::enabled(Features::login())) {
            Route::get('/login', [Auth\LoginController::class, 'create'])->name('login');
            Route::post('/login', [Auth\LoginController::class, 'store'])->name('login.store');
        }

        if (Features::enabled(Features::social())) {
            Route::get('/social/{provider}', Auth\SocialRedirectController::class)->name('social.redirect');
            Route::get('/social/{provider}/callback', Auth\SocialCallbackController::class)->name('social.callback');
        }

        if (Features::enabled(Features::passwordReset())) {
            Route::get('/forgot-password', [Auth\PasswordResetLinkController::class, 'create'])
                ->name('password.request');

            Route::post('/forgot-password', [Auth\PasswordResetLinkController::class, 'store'])
                ->name('password.email');

            Route::get('/reset-password/{token}', [Auth\NewPasswordController::class, 'create'])
                ->name('password.reset');

            Route::post('/reset-password', [Auth\NewPasswordController::class, 'store'])
                ->name('password.update');
        }
    });

if (Features::enabled(Features::logout())) {
    Route::prefix(AuthPreset::webPrefix())
        ->middleware([...AuthPreset::webMiddleware(), 'auth:' . AuthPreset::guard()])
        ->group(function (): void {
            Route::post('/logout', Auth\LogoutController::class)->name('logout');
        });
}

if (Features::enabled(Features::emailVerification())) {
    Route::prefix(AuthPreset::webPrefix())
        ->middleware([...AuthPreset::webMiddleware(), 'auth:' . AuthPreset::guard()])
        ->group(function (): void {
            Route::get('/email/verify', Auth\EmailVerificationPromptController::class)
                ->name('verification.notice');

            Route::get('/email/verify/{id}/{hash}', Auth\VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

            Route::post('/email/verification-notification', [Auth\EmailVerificationNotificationController::class, 'store'])
                ->middleware(['throttle:6,1'])
                ->name('verification.send');
        });
}
