<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractLogoutController;

class LogoutController extends AbstractLogoutController
{
    protected function guard(): string
    {
        return \Simtabi\Laranail\AuthPreset\Support\AuthPreset::guard();
    }

    protected function loggedOut(Request $request): JsonResponse
    {
        return response()->json(data: [
            'status' => 'logged_out',
        ]);
    }
}
