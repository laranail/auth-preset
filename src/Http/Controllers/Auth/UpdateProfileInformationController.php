<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;

class UpdateProfileInformationController extends ProfileInformationController
{
    public function create(Request $request): View
    {
        return view(AuthPreset::view('update-profile-information'), [
            'request' => $request,
            'user'    => $request->user(),
        ]);
    }
}
