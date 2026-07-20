<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Livewire;

use Livewire\Component;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\Auth\Actions\LoginUser;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Actions\AttemptUsernameLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;

class UsernameLogin extends Component
{
    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public function login(
        AttemptUsernameLogin $action,
        LoginUser $loginUser,
    ): RedirectResponse|Redirector {
        $this->validate(rules: [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $result = $action->execute(
            input: new AttemptUsernameLoginInput(
                username: $this->username,
                password: $this->password,
                remember: $this->remember,
                guard: 'web',
            ),
        );

        if ($result->isPassed()) {
            $loginUser->execute(
                user: $result->user,
                remember: $this->remember,
            );

            return redirect()->intended(AuthPreset::afterLoginRedirect());
        }

        $this->resetErrorBag();
        $this->addError(key: 'username', message: 'Invalid credentials.');

        $this->resetExcept(properties: ['username', 'remember']);

        return redirect()->back();
    }

    public function render(): View
    {
        return view(view: 'auth-preset::livewire.username-login');
    }
}
