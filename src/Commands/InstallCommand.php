<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Commands;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

use Illuminate\Database\Eloquent\Model;

use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
    private const TAILWIND_BLADE_SOURCE = "@source '../../vendor/laravel/laranail/**/*.blade.php';";

    private const PASSKEYS_NPM_PACKAGE = '@laravel/passkeys';

    private const SOCIAL_PROVIDERS = [
        'google'   => 'Google',
        'facebook' => 'Facebook',
        'twitter'  => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'paypal'   => 'PayPal',
    ];
    protected $signature = 'laranail:authkit.install
        {--stack= : The frontend stack to install}
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

        $socialProviders = $this->resolveSocialProviders();
        $wantsApi = $this->resolveApiPreference();
        $wantsPasswordReset = $this->resolvePasswordReset();
        $wantsEmailVerification = $this->resolveEmailVerification();
        $wantsPasskeys = $this->resolvePasskeys();
        $wantsTurnstile = $this->resolveTurnstile();
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

        $this->configureFeatures($socialProviders, $wantsApi, $wantsPasswordReset, $wantsEmailVerification, $wantsPasskeys, $wantsTurnstile);

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

        $this->configureEnvironment($socialProviders, $wantsTurnstile);

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function resolveSocialProviders(): array
    {
        $optionProviders = $this->option('social');

        if (count($optionProviders) > 0) {
            return array_values(array_intersect($optionProviders, array_keys(self::SOCIAL_PROVIDERS)));
        }

        if (! $this->input->isInteractive()) {
            return [];
        }

        return multiselect(
            label: 'Which social login providers would you like to enable?',
            options: self::SOCIAL_PROVIDERS,
            default: [],
            required: false,
            hint: 'Leave empty to skip social login.',
        );
    }

    private function resolveApiPreference(): bool
    {
        if ($this->option('api') !== null) {
            return (bool) $this->option('api');
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(
            label: 'Would you like to enable API authentication with Sanctum tokens?',
            default: false,
            hint: 'Publishes the personal_access_tokens migration and enables API routes.',
        );
    }

    private function resolvePasswordReset(): bool
    {
        if ($this->option('password-reset') !== null) {
            return (bool) $this->option('password-reset');
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(
            label: 'Would you like to enable password reset?',
            default: true,
            hint: 'Adds forgot-password and reset-password views and routes via Fortify.',
        );
    }

    private function resolveEmailVerification(): bool
    {
        if ($this->option('email-verification') !== null) {
            return (bool) $this->option('email-verification');
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(
            label: 'Would you like to enable email verification?',
            default: true,
            hint: 'Sends a verification email after registration via Fortify.',
        );
    }

    private function resolvePasskeys(): bool
    {
        if ($this->input->hasParameterOption('--passkeys')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(
            label: 'Would you like to enable passkey authentication?',
            default: false,
            hint: 'Publishes the passkeys migration and enables the Fortify passkey UI.',
        );
    }

    private function resolveTurnstile(): bool
    {
        if ($this->input->hasParameterOption('--turnstile')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(
            label: 'Would you like to enable Cloudflare Turnstile validation on guest forms?',
            default: false,
            hint: 'Requires TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY in your environment.',
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

    /** @param array<int, string> $providers */
    private function configureFeatures(array $providers, bool $wantsApi, bool $wantsPasswordReset, bool $wantsEmailVerification, bool $wantsPasskeys, bool $wantsTurnstile): void
    {
        if (count($providers) === 0 && ! $wantsApi && ! $wantsPasswordReset && ! $wantsEmailVerification && ! $wantsPasskeys && ! $wantsTurnstile) {
            return;
        }

        $configPath = config_path('auth-preset.php');

        if (! file_exists($configPath)) {
            return;
        }

        $contents = file_get_contents($configPath);

        if (count($providers) > 0 && ! str_contains($contents, 'Features::social()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::login\(\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::social(),", $contents, limit: 1);
        }

        if ($wantsPasswordReset && ! str_contains($contents, 'Features::passwordReset()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::logout\(\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::passwordReset(),", $contents, limit: 1);
        }

        if ($wantsEmailVerification && ! str_contains($contents, 'Features::emailVerification()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::logout\(\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::emailVerification(),", $contents, limit: 1);
        }

        if ($wantsApi && ! str_contains($contents, 'Features::api()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::logout\(\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::api(),", $contents, limit: 1);
        }

        if ($wantsPasskeys && ! str_contains($contents, 'Features::passkeys()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::logout\(\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::passkeys(),", $contents, limit: 1);
        }

        if ($wantsTurnstile && ! str_contains($contents, 'Features::turnstile()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::logout\\(\\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::turnstile(),", $contents, limit: 1);
        }

        if (count($providers) > 0) {
            $providerArray = "['" . implode("', '", $providers) . "']";
            $pattern = "/'providers'\s*=>\s*\[[^\]]*\]/";
            $contents = preg_replace($pattern, "'providers' => {$providerArray}", $contents);
        }

        file_put_contents($configPath, $contents);
    }

    /** @param array<int, string> $providers */
    private function configureEnvironment(array $providers, bool $wantsTurnstile, ?string $envPath = null, ?string $envExamplePath = null): void
    {
        $envPath ??= base_path('.env');
        $envExamplePath ??= base_path('.env.example');
        $variables = [];

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
