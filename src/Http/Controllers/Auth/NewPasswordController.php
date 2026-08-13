<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractNewPasswordController;

class NewPasswordController extends AbstractNewPasswordController
{
    public function create(Request $request): View
    {
        return view(AuthPreset::view('reset-password'), [
            'request' => $request,
        ]);
    }
}
