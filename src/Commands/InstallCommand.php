<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Commands;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;

use Illuminate\Database\Eloquent\Model;

use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
    private const TAILWIND_BLADE_SOURCE = "@source '../../vendor/laravel/laranail/**/*.blade.php';";

    private const PASSKEYS_NPM_PACKAGE = '@laravel/passkeys';

    private const AUTHENTICATION_FEATURES = [
        'login'                      => 'Login',
        'registration'               => 'Registration',
        'logout'                     => 'Logout',
        'update-profile-information' => 'Profile information updates',
        'update-passwords'           => 'Password updates',
        'social'                     => 'Social login',
        'api'                        => 'API authentication',
        'password-reset'             => 'Password reset',
        'email-verification'         => 'Email verification',
        'passkeys'                   => 'Passkey authentication',
        'turnstile'                  => 'Cloudflare Turnstile',
    ];

    private const FEATURE_DESCRIPTIONS = [
        'login'                      => 'Adds the web login form and authentication endpoint.',
        'registration'               => 'Adds the web registration form and account creation endpoint.',
        'logout'                     => 'Adds the web logout endpoint for authenticated users.',
        'update-profile-information' => 'Adds the authenticated profile information form and endpoint.',
        'update-passwords'           => 'Adds the authenticated password update form and endpoint.',
        'social'                     => 'Adds OAuth callback routes for the providers selected next.',
        'api'                        => 'Adds Sanctum token authentication routes and publishes its migration.',
        'password-reset'             => 'Adds forgot-password and reset-password views and routes.',
        'email-verification'         => 'Sends and verifies registration email addresses through Fortify.',
        'passkeys'                   => 'Adds passkey routes, migration, and the official browser client.',
        'turnstile'                  => 'Protects your frontend forms from bots with Cloudflare Turnstile.',
    ];

    private const SOCIAL_PROVIDERS = [
        'google'   => 'Google',
        'facebook' => 'Facebook',
        'twitter'  => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'paypal'   => 'PayPal',
    ];
    protected $signature = 'laranail:authkit.install
        {--stack= : The frontend stack to install}
        {--guard= : The authentication guard to use}
        {--social=* : Social providers to enable (google, facebook, twitter, linkedin, paypal)}
        {--api : Enable API authentication with Sanctum tokens}
        {--password-reset : Enable password reset flow}
        {--email-verification : Enable email verification flow}
        {--passkeys : Enable passkey authentication, migration, and browser client}
        {--turnstile : Enable Cloudflare Turnstile validation on guest forms}
        {--model= : The Eloquent authentication model to configure}
        {--publish-routes : Publish package route files for application ownership}
        {--publish-controllers : Reserved for a future controller publishing workflow}
        {--publish-views : Publish Blade views for application ownership}
        {--force : Overwrite existing published files}';

    protected $description = 'Install the Blade auth-preset resources';

    public function handle(): int
    {
        $stack = $this->option('stack') ?? select(
            label: 'Which frontend stack would you like to install?',
            options: ['blade' => 'Blade'],
            default: 'blade',
        );

        if ($stack !== 'blade') {
            $this->error('Only the [blade] stack is currently supported.');

            return self::FAILURE;
        }

        $guard = $this->resolveGuard();

        if ($guard === null) {
            return self::FAILURE;
        }

        $features = $this->resolveFeatures();
        $socialProviders = $this->resolveSocialProviders(in_array('social', $features, true));
        $wantsApi = in_array('api', $features, true);
        $wantsPasswordReset = in_array('password-reset', $features, true);
        $wantsEmailVerification = in_array('email-verification', $features, true);
        $wantsPasskeys = in_array('passkeys', $features, true);
        $wantsTurnstile = in_array('turnstile', $features, true);

        if (count($socialProviders) === 0) {
            $features = array_values(array_diff($features, ['social']));
        } elseif (! in_array('social', $features, true)) {
            $features[] = 'social';
        }

        $authModel = $this->resolveAuthModel($wantsApi, $wantsPasskeys);

        if (($wantsApi || $wantsPasskeys) && $authModel === null) {
            return self::FAILURE;
        }

        if ($authModel !== null && ! $this->configureAuthModel($authModel, $wantsApi, $wantsPasskeys)) {
            return self::FAILURE;
        }

        $this->publish(tag: 'auth-kit-config');
        $this->publish(tag: 'auth-preset-config');
        $this->configureTailwindSource();

        if ($wantsPasskeys) {
            $this->installPasskeyFrontend();
        }

        if (count($socialProviders) > 0) {
            $this->publish(tag: 'auth-kit-social-migrations');
            $this->newLine();
            $this->info('Social login migration published. Run `php artisan migrate` to create the socials table.');
        }

        if ($wantsApi) {
            $this->publish(tag: 'sanctum-migrations');
            $this->newLine();
            $this->info('Sanctum token migration published. Run `php artisan migrate` to create the personal_access_tokens table.');
        }

        if ($wantsPasskeys) {
            $this->publish(tag: 'auth-kit-passkey-migrations');
            $this->newLine();
            $this->info('Passkeys migration published. Run `php artisan migrate` to create the passkeys table.');
            $this->line('The @laravel/passkeys browser client and Blade event handlers were added to resources/js. Run `npm install` and rebuild your frontend assets.');
        }

        if ($authModel !== null) {
            $this->newLine();
            $this->info("Authentication model configured: {$authModel}.");
        }

        if ($wantsApi || $wantsPasskeys) {
            $this->line('Migrations were published to the application database/migrations directory. If this model belongs to a module, move the migrations to that module only if its module system owns migration loading.');
        }

        if ($this->option('publish-routes')) {
            $this->publish('auth-preset-routes');
        }

        if ($this->option('publish-views')) {
            $this->publish('auth-preset-views');
        }

        if ($this->option('publish-controllers')) {
            $this->warn('Controller publishing is not needed yet: extend the package controllers or use auth-kit contracts in your application controller.');
        }

        $this->configureFeatures($features, $socialProviders);

        $this->info('auth-preset is ready. Package routes are registered automatically.');
        $this->line('Visit /auth/register or /auth/login. Review config/auth-preset.php to enable or disable features.');

        if ($wantsApi) {
            $this->line('API routes are enabled at /api/auth. Use Sanctum tokens for authentication.');
        }

        if (count($socialProviders) > 0) {
            $this->newLine();
            $this->info('Social login enabled for: ' . implode(', ', $socialProviders) . '.');
            $this->line('Set your OAuth credentials in .env for each enabled provider.');
        }

        if ($wantsTurnstile) {
            $this->newLine();
            $this->info('Turnstile validation enabled for guest forms.');
            $this->line('Add TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY to your .env file.');
            $this->line('Optionally add TURNSTILE_URL to override the default Cloudflare verification endpoint.');
        }

        $this->configureEnvironment($socialProviders, $wantsTurnstile, guard: $guard);

        return self::SUCCESS;
    }

    private function resolveGuard(): ?string
    {
        $guards = array_keys((array) config('auth.guards', []));

        if (count($guards) === 0) {
            $this->error('No authentication guards were found in config/auth.php.');

            return null;
        }

        $requestedGuard = $this->option('guard');

        if ($requestedGuard !== null) {
            if (! in_array($requestedGuard, $guards, true)) {
                $this->error("The guard [{$requestedGuard}] is not configured in config/auth.php.");

                return null;
            }

            return $requestedGuard;
        }

        $configuredGuard = config('auth-preset.guard', config('auth.defaults.guard'));
        $defaultGuard = is_string($configuredGuard) && in_array($configuredGuard, $guards, true)
            ? $configuredGuard
            : $guards[0];

        if (! $this->input->isInteractive()) {
            return $defaultGuard;
        }

        return select(
            label: 'Which authentication guard would you like to use?',
            options: array_combine($guards, $guards),
            default: $defaultGuard,
        );
    }

    /** @return array<int, string> */
    private function resolveFeatures(): array
    {
        $explicit = [];

        foreach (['api', 'password-reset', 'email-verification', 'passkeys', 'turnstile'] as $feature) {
            if ($this->input->hasParameterOption('--' . $feature)) {
                $explicit[] = $feature;
            }
        }

        if (count($this->option('social')) > 0) {
            $explicit[] = 'social';
        }

        if (! $this->input->isInteractive()) {
            return array_values(array_unique(array_merge([
                'login',
                'registration',
                'logout',
                'update-profile-information',
                'update-passwords',
            ], $explicit)));
        }

        $features = multiselect(
            label: 'Which authentication feature would you like to enable?',
            options: self::AUTHENTICATION_FEATURES,
            default: array_keys(self::AUTHENTICATION_FEATURES),
            scroll: count(self::AUTHENTICATION_FEATURES),
            info: static fn (string $feature): ?string => self::FEATURE_DESCRIPTIONS[$feature] ?? null,
            hint: 'All features are selected by default. Press space to disable features you do not need.',
        );

        return array_values(array_unique(array_merge($features, $explicit)));
    }

    /** @return array<int, string> */
    private function resolveSocialProviders(bool $featureSelected): array
    {
        $optionProviders = $this->option('social');

        if (count($optionProviders) > 0) {
            return array_values(array_intersect($optionProviders, array_keys(self::SOCIAL_PROVIDERS)));
        }

        if (! $featureSelected || ! $this->input->isInteractive()) {
            return [];
        }

        return multiselect(
            label: 'Which social login providers would you like to enable?',
            options: self::SOCIAL_PROVIDERS,
            default: ['google'],
            required: false,
            hint: 'Google is selected by default. Enable only the providers you plan to configure.',
        );
    }

    private function resolveAuthModel(bool $wantsApi, bool $wantsPasskeys): ?string
    {
        if (! $wantsApi && ! $wantsPasskeys) {
            return null;
        }

        $models = $this->eloquentAuthModels();

        if (count($models) === 0) {
            $this->error('Sanctum and passkeys require an Eloquent authentication model. No Eloquent auth provider was found in config/auth.php.');

            return null;
        }

        $requestedModel = $this->option('model');

        if ($requestedModel !== null) {
            if (! array_key_exists($requestedModel, $models)) {
                $this->error("The model [{$requestedModel}] is not configured by an Eloquent auth provider.");

                return null;
            }

            return $requestedModel;
        }

        if (! $this->input->isInteractive()) {
            if (count($models) === 1) {
                return array_key_first($models);
            }

            $this->error('Multiple Eloquent auth models were found. Re-run the installer with --model="App\\Models\\User".');

            return null;
        }

        $options = [];

        foreach ($models as $model => $providers) {
            $options[$model] = $model . ' (' . implode(', ', $providers) . ')';
        }

        return select(
            label: 'Which Eloquent model should receive the authentication traits?',
            options: $options,
            default: array_key_first($models),
        );
    }

    /** @return array<string, array<int, string>> */
    private function eloquentAuthModels(): array
    {
        $models = [];

        foreach ((array) config('auth.providers', []) as $providerName => $provider) {
            if (! is_array($provider) || ($provider['driver'] ?? null) !== 'eloquent') {
                continue;
            }

            $model = $provider['model'] ?? null;

            if (! is_string($model) || $model === '' || ! is_a($model, Model::class, true)) {
                continue;
            }

            $models[$model] ??= [];
            $models[$model][] = (string) $providerName;
        }

        return $models;
    }

    private function configureAuthModel(string $model, bool $wantsApi, bool $wantsPasskeys): bool
    {
        if (! class_exists($model)) {
            $this->error("The configured authentication model [{$model}] could not be loaded.");

            return false;
        }

        $reflection = new ReflectionClass($model);
        $file = $reflection->getFileName();

        if ($file === false) {
            $this->error("The authentication model [{$model}] does not have a writable source file.");

            return false;
        }

        return $this->configureModelFile($file, $reflection->getShortName(), $wantsApi, $wantsPasskeys);
    }

    private function configureModelFile(string $file, string $className, bool $wantsApi, bool $wantsPasskeys): bool
    {
        if (! is_file($file) || ! is_readable($file) || ! is_writable($file)) {
            $this->error("The authentication model file [{$file}] must be readable and writable.");

            return false;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            $this->error("Unable to read the authentication model file [{$file}].");

            return false;
        }

        if ($wantsApi) {
            $contents = $this->addModelImport($contents, 'Laravel\\Sanctum\\HasApiTokens');
            $contents = $this->addModelTrait($contents, $className, 'HasApiTokens');
        }

        if ($wantsPasskeys) {
            $contents = $this->addModelImport($contents, 'Laravel\\Fortify\\Contracts\\PasskeyUser');
            $contents = $this->addModelImport($contents, 'Simtabi\\Laranail\\Auth\\PasskeyAuthenticatable');
            $contents = $this->addModelInterface($contents, $className, 'PasskeyUser');
            $contents = $this->addModelTrait($contents, $className, 'PasskeyAuthenticatable');
        }

        if (file_put_contents($file, $contents) === false) {
            $this->error("Unable to update the authentication model file [{$file}].");

            return false;
        }

        return true;
    }

    private function addModelImport(string $contents, string $import): string
    {
        $shortName = Str::afterLast($import, '\\');

        if (preg_match('/^use\\s+[^;]+\\\\' . preg_quote($shortName, '/') . '(?:\\s+as\\s+' . preg_quote($shortName, '/') . ')?\\s*;/m', $contents) === 1) {
            return $contents;
        }

        $updated = preg_replace(
            '/^(namespace\\s+[^;]+;)(\\R)/m',
            "$1$2use {$import};$2",
            $contents,
            1,
        );

        return $updated ?? $contents;
    }

    private function addModelInterface(string $contents, string $className, string $interface): string
    {
        $updated = preg_replace_callback(
            '/(\\bclass\\s+' . preg_quote($className, '/') . '\\b)([^\\{]*)(\\{)/',
            static function (array $matches) use ($interface): string {
                if (str_contains($matches[2], $interface)) {
                    return implode('', $matches);
                }

                if (preg_match('/implements\\s+([^\\{]+)/', $matches[2]) === 1) {
                    $header = mb_rtrim($matches[2]) . ', ' . $interface;
                    $header .= mb_substr($matches[2], mb_strlen(mb_rtrim($matches[2])));

                    return $matches[1] . $header . $matches[3];
                }

                $header = mb_rtrim($matches[2]) . ' implements ' . $interface;
                $header .= mb_substr($matches[2], mb_strlen(mb_rtrim($matches[2])));

                return $matches[1] . $header . $matches[3];
            },
            $contents,
            1,
        );

        return $updated ?? $contents;
    }

    private function addModelTrait(string $contents, string $className, string $trait): string
    {
        if (preg_match('/^\\s*use\\s+' . preg_quote($trait, '/') . '\\s*;/m', $contents) === 1) {
            return $contents;
        }

        $updated = preg_replace(
            '/(\\bclass\\s+' . preg_quote($className, '/') . '\\b[^\\{]*\\{)(\\R)/',
            "$1$2    use {$trait};$2",
            $contents,
            1,
        );

        return $updated ?? $contents;
    }

    private function configureTailwindSource(?string $cssPath = null): bool
    {
        $cssPath ??= base_path('resources/css/app.css');

        if (! file_exists($cssPath)) {
            return false;
        }

        $contents = file_get_contents($cssPath);

        if (str_contains($contents, self::TAILWIND_BLADE_SOURCE)) {
            return false;
        }

        $frameworkSource = "@source '../../storage/framework/views/*.php';";

        if (str_contains($contents, $frameworkSource)) {
            $replacementCount = 0;
            $contents = str_replace(
                $frameworkSource,
                $frameworkSource . "\n" . self::TAILWIND_BLADE_SOURCE,
                $contents,
                $replacementCount,
            );
        } else {
            $contents = mb_rtrim($contents, "\n") . "\n\n" . self::TAILWIND_BLADE_SOURCE . "\n";
        }

        file_put_contents($cssPath, $contents);

        return true;
    }

    private function installPasskeyFrontend(?string $packagePath = null, ?string $appJsPath = null, ?string $passkeysJsPath = null): bool
    {
        $packagePath ??= base_path('package.json');
        $appJsPath ??= base_path('resources/js/app.js');
        $passkeysJsPath ??= base_path('resources/js/passkeys.js');
        $changed = false;

        if (file_exists($packagePath)) {
            $package = json_decode((string) file_get_contents($packagePath), true);

            if (! is_array($package)) {
                $this->warn('Could not update package.json because it does not contain valid JSON.');
            } elseif (! isset($package['dependencies'][self::PASSKEYS_NPM_PACKAGE])
                && ! isset($package['devDependencies'][self::PASSKEYS_NPM_PACKAGE])) {
                $package['dependencies'] ??= [];
                $package['dependencies'][self::PASSKEYS_NPM_PACKAGE] = '^0.2.0';
                ksort($package['dependencies']);
                file_put_contents($packagePath, json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
                $changed = true;
            }
        }

        $sourcePath = __DIR__ . '/../../resources/js/passkeys.js';

        if (! file_exists($passkeysJsPath) && file_exists($sourcePath)) {
            $directory = dirname($passkeysJsPath);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            copy($sourcePath, $passkeysJsPath);
            $changed = true;
        }

        $import = "import './passkeys';";

        if (! file_exists($appJsPath)) {
            $directory = dirname($appJsPath);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($appJsPath, $import . "\n");
            $changed = true;
        } else {
            $contents = file_get_contents($appJsPath);

            if ($contents !== false && ! preg_match('/import\s+[\'\"]\.\/passkeys[\'\"]\s*;/', $contents)) {
                file_put_contents($appJsPath, mb_rtrim($contents, "\n") . "\n\n" . $import . "\n");
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @param array<int, string> $features
     * @param array<int, string> $providers
     */
    private function configureFeatures(array $features, array $providers, ?string $configPath = null): void
    {
        $configPath ??= config_path('auth-preset.php');

        if (! file_exists($configPath)) {
            return;
        }

        $contents = file_get_contents($configPath);
        $featureMethods = [
            'login'                      => 'login',
            'registration'               => 'registration',
            'logout'                     => 'logout',
            'update-profile-information' => 'updateProfileInformation',
            'update-passwords'           => 'updatePasswords',
            'social'                     => 'social',
            'api'                        => 'api',
            'password-reset'             => 'passwordReset',
            'email-verification'         => 'emailVerification',
            'passkeys'                   => 'passkeys',
            'turnstile'                  => 'turnstile',
        ];
        $featureLines = [];

        foreach ($featureMethods as $feature => $method) {
            if (in_array($feature, $features, true) && ($feature !== 'social' || count($providers) > 0)) {
                $featureLines[] = "        \\Simtabi\\Laranail\\AuthPreset\\Features::{$method}(),";
            }
        }

        $featureBlock = "    'features' => [\n" . implode("\n", $featureLines) . "\n    ],";
        $contents = preg_replace(
            "/    'features'\s*=>\s*\[(?:.|\R)*?\n    \],/",
            $featureBlock,
            $contents,
            1,
        ) ?? $contents;

        $providerArray = "['" . implode("', '", $providers) . "']";
        $contents = preg_replace(
            "/'providers'\s*=>\s*\[[^\]]*\]/",
            "'providers' => {$providerArray}",
            $contents,
            1,
        ) ?? $contents;

        file_put_contents($configPath, $contents);
    }

    /** @param array<int, string> $providers */
    private function configureEnvironment(array $providers, bool $wantsTurnstile, ?string $envPath = null, ?string $envExamplePath = null, ?string $guard = null): void
    {
        $envPath ??= base_path('.env');
        $envExamplePath ??= base_path('.env.example');
        $variables = [];

        if ($guard !== null) {
            $variables['AUTH_PRESET_GUARD'] = $guard;
        }

        foreach ($providers as $provider) {
            $upper = Str::upper($provider);
            $variables["AUTH_KIT_{$upper}_CLIENT_ID"] = '';
            $variables["AUTH_KIT_{$upper}_CLIENT_SECRET"] = '';
            $variables["AUTH_KIT_{$upper}_REDIRECT"] = url("/auth/social/{$provider}/callback");
        }

        if ($wantsTurnstile) {
            $variables['TURNSTILE_SITE_KEY'] = '';
            $variables['TURNSTILE_SECRET_KEY'] = '';
            $variables['TURNSTILE_URL'] = '';
        }

        if (count($variables) === 0) {
            return;
        }

        foreach ([$envPath, $envExamplePath] as $path) {
            $this->appendMissingEnvironmentVariables($path, $variables);
        }
    }

    /** @param array<string, string> $variables */
    private function appendMissingEnvironmentVariables(string $path, array $variables): void
    {
        if (! file_exists($path)) {
            return;
        }

        $existing = file_get_contents($path);

        if ($existing === false) {
            return;
        }

        $missing = [];

        foreach ($variables as $key => $value) {
            if (preg_match('/^\s*(?:export\s+)?' . preg_quote($key, '/') . '\s*=/m', $existing) === 1) {
                continue;
            }

            $missing[$key] = $value;
        }

        if (count($missing) === 0) {
            return;
        }

        $lines = [];

        foreach ($missing as $key => $value) {
            $lines[] = "{$key}={$value}";
        }

        file_put_contents($path, mb_rtrim($existing, "\n") . "\n\n# Auth Kit environment variables\n" . implode("\n", $lines) . "\n");
    }

    private function publish(string $tag): void
    {
        $parameters = ['--tag' => $tag];

        if ($this->option('force')) {
            $parameters['--force'] = true;
        }

        $this->call('vendor:publish', $parameters);
    }
}
