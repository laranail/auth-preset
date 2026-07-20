<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractCheckUsernameExistsController;

class CheckUsernameExistsController extends AbstractCheckUsernameExistsController
{
    protected function respond(Request $request, bool $exists): mixed
    {
        return response()->json(data: ['exists' => $exists]);
    }
}
