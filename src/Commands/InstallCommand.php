<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Commands;

use RecursiveIteratorIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;

class InstallCommand extends Command
{
    protected $signature = 'auth-preset:install
        {--stack= : The frontend stack to scaffold (blade, livewire, inertia-vue, inertia-react)}
        {--force : Overwrite existing files}';

    protected $description = 'Install the auth-preset scaffolding for the selected frontend stack';

    public function handle(): int
    {
        $stack = $this->option('stack') ?? $this->choice(
            question: 'Which frontend stack would you like to install?',
            choices: ['blade', 'livewire', 'inertia-vue', 'inertia-react'],
            default: 0,
        );

        if (! in_array(haystack: $stack, needles: ['blade', 'livewire', 'inertia-vue', 'inertia-react'])) {
            $this->error(error: "Invalid stack [{$stack}]. Available: blade, livewire, inertia-vue, inertia-react");

            return static::FAILURE;
        }

        $force = $this->option('force');

        $this->newLine();
        $this->info(string: "Installing auth-preset with [{$stack}] stack...");
        $this->newLine();

        $this->publishConfig(force: $force);
        $this->publishRoutes(force: $force);

        match ($stack) {
            'blade'         => $this->installBlade(force: $force),
            'livewire'      => $this->installLivewire(force: $force),
            'inertia-vue'   => $this->installInertiaVue(force: $force),
            'inertia-react' => $this->installInertiaReact(force: $force),
        };

        $this->updateEnvStack(stack: $stack);
        $this->registerServiceProvider();

        $this->newLine();
        $this->info(string: "Auth-preset installed successfully with [{$stack}] stack.");
        $this->newLine();

        $this->line(string: 'Next steps:');
        $this->line(string: '  1. Review config/auth-preset.php and enable/disable features');
        $this->line(string: '  2. Ensure your User model implements Authenticatable');
        $this->line(string: '  3. Visit /auth/login to see the login form');

        return static::SUCCESS;
    }

    private function publishConfig(bool $force = false): void
    {
        $tag = $force ? 'auth-preset-config force' : 'auth-preset-config';

        $this->callSilently(
            command: 'vendor:publish',
            parameters: ['--tag' => $tag],
        );
    }

    private function publishRoutes(bool $force = false): void
    {
        $tag = $force ? 'auth-preset-routes force' : 'auth-preset-routes';

        $this->callSilently(
            command: 'vendor:publish',
            parameters: ['--tag' => $tag],
        );
    }

    private function installBlade(bool $force = false): void
    {
        $tag = $force ? 'auth-preset-views force' : 'auth-preset-views';

        $this->callSilently(
            command: 'vendor:publish',
            parameters: ['--tag' => $tag],
        );

        $this->info(string: '  Blade views published.');
    }

    private function installLivewire(bool $force = false): void
    {
        $tag = $force ? 'auth-preset-livewire force' : 'auth-preset-livewire';

        $this->callSilently(
            command: 'vendor:publish',
            parameters: ['--tag' => $tag],
        );

        $this->info(string: '  Livewire components and views published.');
        $this->line(string: '  Register Livewire components in your AppServiceProvider or run:');
        $this->line(string: '    Livewire::component(\'auth-preset.login\', App\\Livewire\\Login::class);');
    }

    private function installInertiaVue(bool $force = false): void
    {
        $tag = $force ? 'auth-preset-inertia force' : 'auth-preset-inertia';

        $this->callSilently(
            command: 'vendor:publish',
            parameters: ['--tag' => $tag],
        );

        // Move vue files into place
        $source = resource_path(path: 'views/inertia/vue');
        $dest = resource_path(path: 'js');

        if (is_dir(directory: $source)) {
            $this->copyDirectory(source: $source, destination: $dest);
            $this->deleteDirectory(path: $source);
        }

        $this->info(string: '  Inertia Vue pages published to resources/js/');
    }

    private function installInertiaReact(bool $force = false): void
    {
        $tag = $force ? 'auth-preset-inertia force' : 'auth-preset-inertia';

        $this->callSilently(
            command: 'vendor:publish',
            parameters: ['--tag' => $tag],
        );

        // Move react files into place
        $source = resource_path(path: 'views/inertia/react');
        $dest = resource_path(path: 'js');

        if (is_dir(directory: $source)) {
            $this->copyDirectory(source: $source, destination: $dest);
            $this->deleteDirectory(path: $source);
        }

        $this->info(string: '  Inertia React pages published to resources/js/');
    }

    private function updateEnvStack(string $stack): void
    {
        $envPath = base_path(path: '.env');

        if (! file_exists(filename: $envPath)) {
            return;
        }

        $env = file_get_contents(filename: $envPath);

        if (str_contains(haystack: $env, needle: 'AUTH_PRESET_STACK=')) {
            $env = preg_replace(
                pattern: '/AUTH_PRESET_STACK=.*/',
                replacement: "AUTH_PRESET_STACK={$stack}",
                subject: $env,
            );
        } else {
            $env .= "\nAUTH_PRESET_STACK={$stack}\n";
        }

        file_put_contents(filename: $envPath, data: $env);
    }

    private function registerServiceProvider(): void
    {
        $appPath = app()->path();
        $providers = $appPath . '/Providers/AppServiceProvider.php';

        if (! file_exists(filename: $providers)) {
            return;
        }

        $content = file_get_contents(filename: $providers);
        $provider = Simtabi\Laranail\AuthPreset\AuthPresetServiceProvider::class;

        if (str_contains(haystack: $content, needle: $provider)) {
            return;
        }

        // Add use statement after last use statement
        $content = preg_replace(
            pattern: '/(use [^;]+;\n)(?!.*use )/',
            replacement: "$1use {$provider};\n",
            subject: $content,
            limit: 1,
        );

        file_put_contents(filename: $providers, data: $content);
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (! is_dir(directory: $destination)) {
            mkdir(directory: $destination, recursive: true, permissions: 0755);
        }

        $items = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $source),
            mode: RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($items as $item) {
            $target = $destination . '/' . mb_ltrim(string: $item->getRelativePathname(), characters: '/');

            if ($item->isDir()) {
                if (! is_dir(directory: $target)) {
                    mkdir(directory: $target, recursive: true, permissions: 0755);
                }
            } else {
                $dir = dirname(path: $target);
                if (! is_dir(directory: $dir)) {
                    mkdir(directory: $dir, recursive: true, permissions: 0755);
                }
                copy(from: (string) $item, to: $target);
            }
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir(directory: $path)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $path),
            mode: RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir(directory: (string) $item);
            } else {
                unlink(filename: (string) $item);
            }
        }

        rmdir(directory: $path);
    }
}
