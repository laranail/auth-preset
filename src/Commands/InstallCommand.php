<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;
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

        $this->publish(tag: 'auth-kit-config');
        $this->publish(tag: 'auth-preset-config');

        if (count($socialProviders) > 0) {
            $this->publish(tag: 'auth-kit-social-migrations');
            $this->newLine();
            $this->info('Social login migration published. Run `php artisan migrate` to create the socials table.');
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

        $this->configureFeatures($socialProviders);

        $this->info('auth-preset is ready. Package routes, including enabled API routes, are registered automatically.');
        $this->line('Visit /auth/register or /auth/login. Review config/auth-preset.php to enable or disable features.');

        if (count($socialProviders) > 0) {
            $this->newLine();
            $this->info('Social login enabled for: ' . implode(', ', $socialProviders) . '.');
            $this->line('Set your OAuth credentials in .env for each enabled provider.');
            $this->writeEnvExample($socialProviders);
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

    /** @param array<int, string> $providers */
    private function configureFeatures(array $providers): void
    {
        if (count($providers) === 0) {
            return;
        }

        $configPath = config_path('auth-preset.php');

        if (! file_exists($configPath)) {
            return;
        }

        $contents = file_get_contents($configPath);

        if (! str_contains($contents, 'Features::social()')) {
            $pattern = "/(\\\\Simtabi\\\\Laranail\\\\AuthPreset\\\\Features::login\(\),)/";
            $contents = preg_replace($pattern, "$1\n        \\Simtabi\\Laranail\\AuthPreset\\Features::social(),", $contents, limit: 1);
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
