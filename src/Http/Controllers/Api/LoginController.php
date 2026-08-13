<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Contracts\LoginUserInterface;
use Simtabi\Laranail\Auth\Contracts\IssueTokenForUserInterface;
use Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface;
use Simtabi\Laranail\Auth\Http\Requests\AttemptEmailPasswordLoginRequest;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptEmailPasswordLoginController;

class LoginController extends AbstractAttemptEmailPasswordLoginController
{
    public function __construct(
        private IssueTokenForUserInterface $issuer,
    ) {
    }

    public function store(
        AttemptEmailPasswordLoginRequest $request,
        AttemptEmailPasswordLoginInterface $attemptAction,
        LoginUserInterface $loginAction,
    ): JsonResponse {
        $result = $attemptAction->execute(
            request: $request,
            guard: $this->guard(),
        );

        return match ($result->status) {
            AuthStatus::Passed    => $this->apiPassed(result: $result),
            AuthStatus::Failed    => $this->jsonResponse(status: 'failed', data: ['message' => 'Invalid credentials.'], code: 422),
            AuthStatus::Throttled => $this->jsonResponse(status: 'throttled', data: ['retry_after' => $result->retryAfterSeconds], code: 429),
        };
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function apiPassed(AuthResult $result): JsonResponse
    {
        $tokenResult = $this->issuer->execute(
            user: $result->user,
            name: 'api-login',
        );

        return $this->jsonResponse(status: 'success', data: [
            'token' => $tokenResult->token,
            'user'  => $tokenResult->user,
        ]);
    }

    protected function passed(Request $request, AuthResult $result): JsonResponse
    {
        $tokenResult = $this->issuer->execute(
            user: $result->user,
            name: 'api-login',
        );

        return response()->json([
            'token' => $tokenResult->token,
            'user'  => $tokenResult->user,
        ]);
    }

    protected function failed(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Invalid credentials.',
        ], 422);
    }

    protected function throttled(Request $request, AuthResult $result): JsonResponse
    {
        return response()->json([
            'status'              => 'throttled',
            'message'             => 'Too many attempts.',
            'retry_after_seconds' => $result->retryAfterSeconds,
        ], 429);
    }
}
