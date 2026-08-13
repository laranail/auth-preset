<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractEmailVerificationPromptController;

class EmailVerificationPromptController extends AbstractEmailVerificationPromptController
{
    protected function prompt(Request $request): mixed
    {
        return view(AuthPreset::view('verify-email'));
    }
}
