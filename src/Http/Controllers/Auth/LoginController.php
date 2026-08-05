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
    public function __construct(
        private LoginUserInterface $loginUser,
    ) {
    }

    public function create(): View
    {
        return view(view: AuthPreset::view(page: 'login'));
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
            remember: $request->boolean(key: 'remember'),
        );

        return redirect()->intended(default: AuthPreset::afterLoginRedirect());
    }

    protected function failed(Request $request, AuthResult $result): RedirectResponse
    {
        return redirect()->back()
            ->withInput(input: $request->only(keys: ['email', 'remember']))
            ->withErrors(provider: ['email' => 'Invalid credentials.']);
    }
}
