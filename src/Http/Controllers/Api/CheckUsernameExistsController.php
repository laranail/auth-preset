<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractCheckUsernameExistsController;

class CheckUsernameExistsController extends AbstractCheckUsernameExistsController
{
    protected function respond(Request $request, bool $exists): JsonResponse
    {
        return response()->json(data: ['exists' => $exists]);
    }
}
