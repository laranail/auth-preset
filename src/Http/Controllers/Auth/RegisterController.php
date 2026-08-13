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
    public function create(): View
    {
        return view(view: AuthPreset::view(page: 'register'));
    }
}
