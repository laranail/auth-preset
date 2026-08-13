<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

it(description: 'offers passkey migration installation', closure: function (): void {
    $command = Artisan::all()['laranail:authkit.install'];

    expect($command->getDefinition()->hasOption('passkeys'))->toBeTrue();
});
