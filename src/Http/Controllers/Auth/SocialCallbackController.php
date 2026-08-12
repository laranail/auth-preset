<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Contracts\LoginUserInterface;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractSocialCallbackController;

class SocialCallbackController extends AbstractSocialCallbackController
{
    public function __construct(
        private LoginUserInterface $loginUser,
    ) {
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function passed(Request $request, AuthResult $result): RedirectResponse
    {
        $this->loginUser->execute(
            user: $result->user,
            guard: $this->guard(),
        );

        return redirect()->intended(default: AuthPreset::afterSocialLoginRedirect());
    }

    protected function failed(Request $request, AuthResult $result): RedirectResponse
    {
        return redirect()->to(path: route('login'))
            ->withErrors(provider: ['email' => 'Social authentication failed.']);
    }
}
