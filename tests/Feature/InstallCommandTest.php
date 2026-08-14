<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\ArrayInput;
use Simtabi\Laranail\AuthPreset\Commands\InstallCommand;

it(description: 'offers passkey migration installation', closure: function (): void {
    $command = Artisan::all()['laranail:authkit.install'];

    expect($command->getDefinition()->hasOption('passkeys'))->toBeTrue();
});

it('offers Turnstile installation and keeps it disabled for non-interactive installs', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];

    expect($command->getDefinition()->hasOption('turnstile'))->toBeTrue();

    $reflection = new ReflectionClass(InstallCommand::class);
    $inputProperty = $reflection->getParentClass()->getProperty('input');
    $resolver = $reflection->getMethod('resolveTurnstile');

    $inputProperty->setValue($command, new ArrayInput([], $command->getDefinition()));
    $inputProperty->getValue($command)->setInteractive(false);

    expect($resolver->invoke($command))->toBeFalse();

    $inputProperty->setValue($command, new ArrayInput(['--turnstile' => true], $command->getDefinition()));
    $inputProperty->getValue($command)->setInteractive(false);

    expect($resolver->invoke($command))->toBeTrue();
});
