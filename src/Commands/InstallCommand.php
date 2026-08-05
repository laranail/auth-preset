<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'auth-preset:install
        {--stack=blade : The frontend stack to install (only blade is currently supported)}
        {--publish-routes : Publish package route files for application ownership}
        {--publish-controllers : Reserved for a future controller publishing workflow}
        {--publish-views : Publish Blade views for application ownership}
        {--force : Overwrite existing published files}';

    protected $description = 'Install the Blade auth-preset resources';

    public function handle(): int
    {
        if ($this->option('stack') !== 'blade') {
            $this->error('Only the [blade] stack is currently supported.');

            return self::FAILURE;
        }

        $this->publish(tag: 'auth-kit-config');
        $this->publish(tag: 'auth-preset-config');

        if ($this->option('publish-routes')) {
            $this->publish('auth-preset-routes');
        }

        if ($this->option('publish-views')) {
            $this->publish('auth-preset-views');
        }

        if ($this->option('publish-controllers')) {
            $this->warn('Controller publishing is not needed yet: extend the package controllers or use auth-kit contracts in your application controller.');
        }

        $this->info('auth-preset is ready. Package routes, including enabled API routes, are registered automatically.');
        $this->line('Visit /auth/register or /auth/login. Review config/auth-preset.php to enable or disable features.');

        return self::SUCCESS;
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
