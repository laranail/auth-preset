<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

    public function create(): View
    {
        return view(view: AuthPreset::view(page: 'register'));
    }

    protected function guard(): string
    {
        return AuthPreset::guard();
    }

    protected function registered(Request $request, Authenticatable $user): RedirectResponse
    {
        $this->login->execute(user: $user, guard: $this->guard());

        return redirect()->to(path: AuthPreset::afterRegistrationRedirect());
    }
}
