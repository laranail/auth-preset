<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Livewire;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;
use Simtabi\Laranail\Auth\Actions\CheckEmailExists as CheckEmailExistsAction;

class CheckEmailExists extends Component
{
    public string $email = '';

    public ?bool $exists = null;

    public function updatedEmail(): void
    {
        if (blank(value: $this->email) || ! filter_var(value: $this->email, filter: FILTER_VALIDATE_EMAIL)) {
            $this->exists = null;

            return;
        }

        $this->exists = app(CheckEmailExistsAction::class)->execute(
            input: new CheckEmailExistsInput(
                email: $this->email,
                guard: 'web',
            ),
        );
    }

    public function render(): View
    {
        return view(view: 'auth-preset::livewire.check-email-exists');
    }
}
