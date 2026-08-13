<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

if (Features::enabled(Features::api())) {
    Route::prefix(AuthPreset::apiPrefix())
        ->middleware(AuthPreset::apiMiddleware())
        ->group(function (): void {
            if (Features::enabled(Features::registration())) {
                Route::post('/register', [Api\RegisterController::class, 'store'])->name('api.register');
            }

            if (Features::enabled(Features::login())) {
                Route::post('/login', [Api\LoginController::class, 'store'])->name('api.login');
            }

            if (Features::enabled(Features::logout())) {
                Route::post('/logout', Api\LogoutController::class)
                    ->middleware('auth:sanctum')
                    ->name('api.logout');
            }

            if (Features::enabled(Features::emailVerification())) {
                Route::post('/email/verification-notification', [Api\EmailVerificationNotificationController::class, 'store'])
                    ->middleware(['auth:sanctum', 'throttle:6,1'])
                    ->name('api.verification.send');

                Route::get('/email/verify/{id}/{hash}', Api\VerifyEmailController::class)
                    ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
                    ->name('api.verification.verify');
            }

            if (Features::enabled(Features::passwordReset())) {
                Route::post('/forgot-password', [Api\PasswordResetLinkController::class, 'store'])
                    ->name('api.password.email');

                Route::post('/reset-password', [Api\NewPasswordController::class, 'store'])
                    ->name('api.password.update');
            }
        });
}
