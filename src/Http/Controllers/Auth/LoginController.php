<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Actions\LoginUser;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptEmailPasswordLoginController;

class LoginController extends AbstractAttemptEmailPasswordLoginController
{
    public function __construct(
        private LoginUser $loginUser,
    ) {
    }

    protected function passed(Request $request, AuthResult $result): mixed
    {
        $this->loginUser->execute(
            user: $result->user,
            remember: (bool) $request->validated(key: 'remember', default: false),
        );

        return redirect()->intended(destination: AuthPreset::afterLoginRedirect());
    }

    protected function failed(Request $request, AuthResult $result): mixed
    {
        return redirect()->back()
            ->withInput(input: $request->only(keys: ['email', 'remember']))
            ->withErrors(errors: ['email' => 'Invalid credentials.']);
    }
}
