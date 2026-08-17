<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\ArrayInput;
use Simtabi\Laranail\AuthPreset\Commands\InstallCommand;

it('offers one feature selection with API, passkeys, and Turnstile choices', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];

    expect($command->getDefinition()->hasOption('api'))->toBeTrue()
        ->and($command->getDefinition()->hasOption('passkeys'))->toBeTrue()
        ->and($command->getDefinition()->hasOption('turnstile'))->toBeTrue()
        ->and($command->getDefinition()->hasOption('guard'))->toBeTrue();

    $reflection = new ReflectionClass(InstallCommand::class);
    $inputProperty = $reflection->getParentClass()->getProperty('input');
    $resolver = $reflection->getMethod('resolveFeatures');

    $inputProperty->setValue($command, new ArrayInput([], $command->getDefinition()));
    $inputProperty->getValue($command)->setInteractive(false);

    expect($resolver->invoke($command))->toBe([
        'login',
        'registration',
        'logout',
        'update-profile-information',
        'update-passwords',
    ]);

    $guardResolver = $reflection->getMethod('resolveGuard');
    config()->set('auth.guards', [
        'web'   => ['driver' => 'session'],
        'admin' => ['driver' => 'session'],
    ]);
    config()->set('auth-preset.guard', 'web');

    expect($guardResolver->invoke($command))->toBe('web');

    $inputProperty->setValue($command, new ArrayInput([
        '--guard'              => 'admin',
        '--api'                => true,
        '--passkeys'           => true,
        '--turnstile'          => true,
        '--email-verification' => true,
        '--password-reset'     => true,
    ], $command->getDefinition()));
    $inputProperty->getValue($command)->setInteractive(false);

    expect($guardResolver->invoke($command))->toBe('admin');

    expect($resolver->invoke($command))->toContain('api')
        ->toContain('passkeys')
        ->toContain('turnstile')
        ->toContain('email-verification')
        ->toContain('password-reset');
});

it('uses Enumerator feature metadata for interactive feature choices', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $features = $reflection->getMethod('authenticationFeatures');
    $descriptions = $reflection->getMethod('featureDescriptions');

    expect($features->invoke($command))->toMatchArray([
        'login'  => 'Login',
        'social' => 'Social login',
    ])
        ->and($descriptions->invoke($command)['social'])
        ->toBe('Adds OAuth callback routes for the providers selected next.');
});

it('uses the laranail console prompter for interactive selections', function (): void {
    $source = file_get_contents(dirname(__DIR__, 2) . '/src/Commands/InstallCommand.php');

    expect(function_exists('prompter'))->toBeTrue()
        ->and(prompter()->getPrompts()->has('select'))->toBeTrue()
        ->and(prompter()->getPrompts()->has('multiselect'))->toBeTrue()
        ->and($source)->not->toContain('Laravel\\Prompts')
        ->and(mb_substr_count($source, 'prompter()->select'))->toBe(3)
        ->and(mb_substr_count($source, 'prompter()->multiselect'))->toBe(2);
});

it('writes the selected feature set without retaining deselected features', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $configurator = $reflection->getMethod('configureFeatures');
    $configPath = tempnam(dirname(__DIR__, 2), 'auth-preset-config-');
    $source = file_get_contents(dirname(__DIR__, 2) . '/config/auth-preset.php');

    file_put_contents($configPath, $source);

    try {
        $configurator->invoke($command, ['login', 'registration', 'api'], [], $configPath);
        $contents = file_get_contents($configPath);

        expect($contents)
            ->toContain('Features::login()')
            ->toContain('Features::registration()')
            ->toContain('Features::api()')
            ->not->toContain('Features::logout()')
            ->not->toContain('Features::passwordReset()')
            ->not->toContain('Features::turnstile()');
    } finally {
        unlink($configPath);
    }
});

it('selects the configured Eloquent model and supports explicit non-interactive selection', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $inputProperty = $reflection->getParentClass()->getProperty('input');
    $resolver = $reflection->getMethod('resolveAuthModel');

    config()->set('auth.providers', [
        'users' => [
            'driver' => 'eloquent',
            'model'  => Workbench\App\Models\User::class,
        ],
        'admins' => [
            'driver' => 'database',
            'table'  => 'admins',
        ],
    ]);

    $inputProperty->setValue($command, new ArrayInput([], $command->getDefinition()));
    $inputProperty->getValue($command)->setInteractive(false);

    expect($resolver->invoke($command, true, false))->toBe(Workbench\App\Models\User::class);

    $inputProperty->setValue($command, new ArrayInput([
        '--model' => Workbench\App\Models\User::class,
    ], $command->getDefinition()));
    $inputProperty->getValue($command)->setInteractive(false);

    expect($resolver->invoke($command, false, true))->toBe(Workbench\App\Models\User::class);
});

it('adds Sanctum and passkey support to a selected model only once', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $configurator = $reflection->getMethod('configureModelFile');
    $modelPath = tempnam(dirname(__DIR__, 2), 'auth-preset-model-');

    file_put_contents(
        $modelPath,
        <<<'PHP'
<?php

namespace Modules\Accounts\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
}
PHP
    );

    try {
        expect($configurator->invoke($command, $modelPath, 'User', true, true))->toBeTrue();

        $contents = file_get_contents($modelPath);

        expect($contents)
            ->toContain('use Laravel\\Sanctum\\HasApiTokens;')
            ->toContain('use Laravel\\Fortify\\Contracts\\PasskeyUser;')
            ->toContain('use Simtabi\\Laranail\\Auth\\PasskeyAuthenticatable;')
            ->toContain('class User extends Authenticatable implements PasskeyUser')
            ->toContain('    use HasApiTokens;')
            ->toContain('    use PasskeyAuthenticatable;');

        expect($configurator->invoke($command, $modelPath, 'User', true, true))->toBeTrue()
            ->and(mb_substr_count(file_get_contents($modelPath), 'use Laravel\\Sanctum\\HasApiTokens;'))->toBe(1)
            ->and(mb_substr_count(file_get_contents($modelPath), 'use PasskeyAuthenticatable;'))->toBe(1);
    } finally {
        unlink($modelPath);
    }
});

it('adds the auth-preset Blade source to app.css only once', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $configurator = $reflection->getMethod('configureTailwindSource');
    $cssPath = tempnam(dirname(__DIR__, 2), 'auth-preset-app-css-');

    file_put_contents(
        $cssPath,
        <<<'CSS'
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';

@theme {
    --font-sans: ui-sans-serif;
}
CSS
    );

    try {
        expect($configurator->invoke($command, $cssPath))->toBeTrue();

        $contents = file_get_contents($cssPath);
        $source = "@source '../../vendor/laravel/laranail/**/*.blade.php';";

        expect($contents)->toContain($source)
            ->toContain("@source '../../storage/framework/views/*.php';\n{$source}");

        expect($configurator->invoke($command, $cssPath))->toBeFalse()
            ->and(mb_substr_count(file_get_contents($cssPath), $source))->toBe(1);
    } finally {
        unlink($cssPath);
    }
});

it('installs the passkey browser client and app entrypoint idempotently', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $installer = $reflection->getMethod('installPasskeyFrontend');
    $directory = dirname(__DIR__, 2) . '/.tmp-passkey-frontend-' . uniqid();
    $packagePath = $directory . '/package.json';
    $appJsPath = $directory . '/resources/js/app.js';
    $passkeysJsPath = $directory . '/resources/js/passkeys.js';

    mkdir(dirname($appJsPath), 0755, true);
    file_put_contents($packagePath, "{\n    \"private\": true,\n    \"devDependencies\": {}\n}\n");
    file_put_contents($appJsPath, "import './bootstrap';\n");

    try {
        expect($installer->invoke($command, $packagePath, $appJsPath, $passkeysJsPath))->toBeTrue();

        $package = json_decode(file_get_contents($packagePath), true);
        $app = file_get_contents($appJsPath);
        $passkeys = file_get_contents($passkeysJsPath);

        expect($package['dependencies']['@laravel/passkeys'])->toBe('^0.2.0')
            ->and($app)->toContain("import './passkeys';")
            ->and($passkeys)->toContain("import { Passkeys } from '@laravel/passkeys';")
            ->and($passkeys)->toContain('Passkeys.verify')
            ->and($passkeys)->toContain('Passkeys.register');

        expect($installer->invoke($command, $packagePath, $appJsPath, $passkeysJsPath))->toBeFalse()
            ->and(mb_substr_count(file_get_contents($appJsPath), "import './passkeys';"))->toBe(1);
    } finally {
        unlink($packagePath);
        unlink($appJsPath);
        unlink($passkeysJsPath);
        rmdir(dirname($appJsPath));
        rmdir(dirname(dirname($appJsPath)));
        rmdir($directory);
    }
});

it('adds selected social and Turnstile environment variables to both env files without overwriting them', function (): void {
    $command = Artisan::all()['laranail:authkit.install'];
    $reflection = new ReflectionClass(InstallCommand::class);
    $configurator = $reflection->getMethod('configureEnvironment');
    $envPath = tempnam(dirname(__DIR__, 2), 'auth-preset-env-');
    $envExamplePath = tempnam(dirname(__DIR__, 2), 'auth-preset-env-example-');

    file_put_contents($envPath, "APP_KEY=existing\nAUTH_KIT_GOOGLE_CLIENT_ID=existing-client\nTURNSTILE_SITE_KEY=existing-site\n");
    file_put_contents($envExamplePath, "APP_KEY=\n");

    try {
        $configurator->invoke($command, ['google', 'linkedin'], true, $envPath, $envExamplePath, 'web');

        foreach ([$envPath, $envExamplePath] as $path) {
            $contents = file_get_contents($path);

            expect($contents)
                ->toContain('AUTH_PRESET_GUARD=web')
                ->toContain('AUTH_KIT_GOOGLE_CLIENT_ID=')
                ->toContain('AUTH_KIT_GOOGLE_CLIENT_SECRET=')
                ->toContain('AUTH_KIT_GOOGLE_REDIRECT=http://localhost/auth/social/google/callback')
                ->toContain('AUTH_KIT_LINKEDIN_CLIENT_ID=')
                ->toContain('AUTH_KIT_LINKEDIN_CLIENT_SECRET=')
                ->toContain('AUTH_KIT_LINKEDIN_REDIRECT=http://localhost/auth/social/linkedin/callback')
                ->toContain('TURNSTILE_SITE_KEY=')
                ->toContain('TURNSTILE_SECRET_KEY=')
                ->not->toContain('TURNSTILE_URL=');
        }

        expect(file_get_contents($envPath))
            ->toContain('AUTH_KIT_GOOGLE_CLIENT_ID=existing-client')
            ->toContain('TURNSTILE_SITE_KEY=existing-site')
            ->and(mb_substr_count(file_get_contents($envPath), 'TURNSTILE_SITE_KEY='))->toBe(1);

        $configurator->invoke($command, ['google', 'linkedin'], true, $envPath, $envExamplePath);

        expect(mb_substr_count(file_get_contents($envPath), 'AUTH_KIT_GOOGLE_CLIENT_ID='))->toBe(1)
            ->and(mb_substr_count(file_get_contents($envExamplePath), 'TURNSTILE_URL='))->toBe(0);
    } finally {
        unlink($envPath);
        unlink($envExamplePath);
    }
});
