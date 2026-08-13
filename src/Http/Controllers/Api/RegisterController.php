<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Contracts\LoginUserInterface;
use Simtabi\Laranail\Auth\Contracts\IssueTokenForUserInterface;
use Laravel\Fortify\Contracts\CreatesNewUsers as FortifyCreateNewUser;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractRegisterController;

class RegisterController extends AbstractRegisterController
{
    public function __construct(
        private IssueTokenForUserInterface $issuer,
    ) {
    }

    public function store(
        Request $request,
        FortifyCreateNewUser $creator,
        LoginUserInterface $loginAction,
    ): JsonResponse {
        event(new Registered($user = $creator->create($request->all())));

        $tokenResult = $this->issuer->execute(
            user: $user,
            name: 'api-register',
        );

        return $this->jsonResponse(status: 'success', data: [
            'token' => $tokenResult->token,
            'user'  => $tokenResult->user,
        ], code: 201);
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function registered(Request $request, Authenticatable $user): JsonResponse
    {
        $tokenResult = $this->issuer->execute(
            user: $user,
            name: 'api-register',
        );

        return response()->json([
            'token' => $tokenResult->token,
            'user'  => $tokenResult->user,
        ], 201);
    }
}
