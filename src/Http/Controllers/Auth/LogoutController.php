<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractLogoutController;

class LogoutController extends AbstractLogoutController
{
    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function loggedOut(Request $request): RedirectResponse
    {
        return redirect()->to(path: AuthPreset::afterLogoutRedirect());
    }
}
