<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Dtos\IssueTokenForUserInput;
use Simtabi\Laranail\Auth\Contracts\IssueTokenForUserInterface;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractRegisterController;

class RegisterController extends AbstractRegisterController
{
    public function __construct(
        private IssueTokenForUserInterface $issuer,
    ) {
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function registered(Request $request, Authenticatable $user): JsonResponse
    {
        $tokenResult = $this->issuer->execute(new IssueTokenForUserInput(
            user: $user,
            name: 'api-register',
        ));

        return response()->json([
            'token' => $tokenResult->token,
            'user'  => $tokenResult->user,
        ], 201);
    }
}
