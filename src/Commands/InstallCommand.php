<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
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
        {--passkeys : Enable passkey authentication and publish its migration}
        {--turnstile : Enable Cloudflare Turnstile validation on guest forms}
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

        $this->publish(tag: 'auth-kit-config');
        $this->publish(tag: 'auth-preset-config');

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
            $this->writeEnvExample($socialProviders);
        }

        if ($wantsTurnstile) {
            $this->newLine();
            $this->info('Turnstile validation enabled for guest forms.');
            $this->line('Add TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY to your .env file.');
            $this->line('Optionally add TURNSTILE_URL to override the default Cloudflare verification endpoint.');
        }

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
            default: true,
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
    private function writeEnvExample(array $providers): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $existing = file_get_contents($envPath);
        $toAppend = '';

        foreach ($providers as $provider) {
            $upper = Str::upper($provider);
            if (str_contains($existing, "AUTH_KIT_{$upper}_CLIENT_ID")) {
                continue;
            }
            $toAppend .= "AUTH_KIT_{$upper}_CLIENT_ID=\n";
            $toAppend .= "AUTH_KIT_{$upper}_CLIENT_SECRET=\n";
            $toAppend .= "AUTH_KIT_{$upper}_REDIRECT=" . url("/auth/social/{$provider}/callback") . "\n";
        }

        if ($toAppend !== '') {
            file_put_contents($envPath, mb_rtrim($existing, "\n") . "\n\n# Social login credentials (auth-kit)\n" . $toAppend);
        }
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
