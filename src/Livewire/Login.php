<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Livewire;

use Livewire\Component;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\Auth\Actions\LoginUser;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(
        AttemptEmailPasswordLogin $action,
        LoginUser $loginUser,
    ): RedirectResponse|Redirector {
        $this->validate(rules: [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $action->execute(
            input: new AttemptEmailPasswordLoginInput(
                email: $this->email,
                password: $this->password,
                remember: $this->remember,
            ),
        );

        if ($result->isPassed()) {
            $loginUser->execute(
                user: $result->user,
                remember: $this->remember,
            );

            return redirect()->intended(destination: AuthPreset::afterLoginRedirect());
        }

        $this->resetErrorBag();
        $this->addError(key: 'email', message: 'Invalid credentials.');

        $this->resetExcept(properties: ['email', 'remember']);

        return redirect()->back();
    }

    public function render(): View
    {
        return view(view: 'auth-preset::livewire.login');
    }
}
