<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractSocialRedirectController;

class SocialRedirectController extends AbstractSocialRedirectController
{
    protected function guard(): string
    {
        return \Simtabi\Laranail\AuthPreset\Support\AuthPreset::guard();
    }

    protected function redirect(Request $request, string $url): JsonResponse
    {
        return response()->json(data: [
            'status'      => 'redirect',
            'redirect_to' => $url,
        ]);
    }
}
