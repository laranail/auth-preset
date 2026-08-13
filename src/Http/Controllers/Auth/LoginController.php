<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Contracts\LoginUserInterface;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptEmailPasswordLoginController;

class LoginController extends AbstractAttemptEmailPasswordLoginController
{
    public function create(): View
    {
        return view(view: AuthPreset::view(page: 'login'));
    }
}
