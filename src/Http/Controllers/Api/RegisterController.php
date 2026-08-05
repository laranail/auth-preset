<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Contracts\LoginUserInterface;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractRegisterController;

class RegisterController extends AbstractRegisterController
{
    public function __construct(
        private LoginUserInterface $login,
    ) {
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function registered(Request $request, Authenticatable $user): JsonResponse
    {
        $this->login->execute($user, $this->guard());

        return response()->json([
            'status' => 'registered',
            'user'   => $user,
        ], 201);
    }
}
