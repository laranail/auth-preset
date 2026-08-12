<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Contracts\IssueTokenForUserInterface;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractSocialCallbackController;

class SocialCallbackController extends AbstractSocialCallbackController
{
    public function __construct(
        private IssueTokenForUserInterface $issuer,
    ) {
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function passed(Request $request, AuthResult $result): JsonResponse
    {
        $tokenResult = $this->issuer->execute(
            user: $result->user,
            name: 'api-social',
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
            'message' => 'Social authentication failed.',
        ], 422);
    }
}
