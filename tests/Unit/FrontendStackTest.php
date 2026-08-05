<?php

declare(strict_types=1);

use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;

it('supports the Blade frontend stack', function (): void {
    expect(FrontendStack::Blade->value)->toBe('blade')
        ->and(FrontendStack::from('blade'))->toBe(FrontendStack::Blade);
});
