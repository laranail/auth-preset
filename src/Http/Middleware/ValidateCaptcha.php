<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\Captcha\Rules\Captcha;

class ValidateCaptcha
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! Features::enabled(Features::botProtection())) {
            return $next($request);
        }

        Validator::make(
            data: $request->all(),
            rules: ['captcha' => ['required', new Captcha()]],
        )->validate();

        return $next($request);
    }
}
