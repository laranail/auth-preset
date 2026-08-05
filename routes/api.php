<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\LoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\RegisterController;

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
    });
}
