<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptUsernameLoginController;

class UsernameLoginController extends AbstractAttemptUsernameLoginController
{
    protected function passed(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json(data: [
            'status' => 'passed',
            'user'   => $result->user,
        ]);
    }

    protected function failed(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json(data: [
            'status'  => 'failed',
            'message' => 'Invalid credentials.',
        ], status: 422);
    }

    protected function throttled(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json(data: [
            'status'              => 'throttled',
            'message'             => 'Too many attempts.',
            'retry_after_seconds' => $result->retryAfterSeconds,
        ], status: 429);
    }
}
