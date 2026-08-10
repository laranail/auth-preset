<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractSocialRedirectController;

class SocialRedirectController extends AbstractSocialRedirectController
{
    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function redirect(Request $request, string $url): RedirectResponse
    {
        return redirect()->to(path: $url);
    }
}
