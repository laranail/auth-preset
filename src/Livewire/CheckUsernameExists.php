<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Livewire;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput;
use Simtabi\Laranail\Auth\Actions\CheckUsernameExists as CheckUsernameExistsAction;

class CheckUsernameExists extends Component
{
    public string $username = '';

    public ?bool $exists = null;

    public function updatedUsername(): void
    {
        if (blank(value: $this->username)) {
            $this->exists = null;

            return;
        }

        $this->exists = app(CheckUsernameExistsAction::class)->execute(
            input: new CheckUsernameExistsInput(
                username: $this->username,
                guard: 'web',
            ),
        );
    }

    public function render(): View
    {
        return view(view: 'auth-preset::livewire.check-username-exists');
    }
}
