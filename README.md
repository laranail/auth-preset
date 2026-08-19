# Laravel Auth Preset

Blade authentication scaffolding for Laravel 13+, powered by [`laranail/auth-kit`](https://github.com/laranail/auth-kit).

The preset provides configurable web and API authentication routes, Fortify-backed password and profile flows, Blade views, social login integration, passkeys, and optional captcha-based bot protection.

## Requirements

- PHP 8.4+
- Laravel 13.x

## Installation

Install the preset with Composer:

```bash
composer require laranail/auth-preset
```

Run the installer and follow the prompts:

```bash
php artisan laranail:authkit.install
```

The installer currently supports the Blade stack. It asks which auth provider should receive authentication traits, then presents one multi-select prompt for authentication features. All available features are selected by default; use the prompt's space key to disable features you do not need. Package routes are registered automatically by default.

After installation, the default web routes are available under `/auth`:

```text
/auth/login
/auth/register
/auth/forgot-password
/auth/reset-password/{token}
```

If API authentication is enabled, its routes are available under `/api/auth` and use Sanctum tokens.

## Installer options

The installer can be run non-interactively with explicit options:

```bash
php artisan laranail:authkit.install \
    --password-reset \
    --email-verification \
    --api \
    --passkeys \
    --model='App\Models\User' \
    --bot-protection \
    --social=google \
    --social=linkedin
```

Available options:

| Option | Description |
| --- | --- |
| `--stack=blade` | Select the frontend stack. Blade is currently supported. |
| `--social=<provider>` | Enable a supported social provider. Repeat for multiple providers. |
| `--api` | Enable API authentication and publish the Sanctum token migration. |
| `--password-reset` | Enable forgot-password and reset-password flows. |
| `--email-verification` | Enable email verification. |
| `--passkeys` | Enable passkey authentication, migration, and browser client. |
| `--model=<class>` | Select the Eloquent auth model to configure for Sanctum and/or passkeys. |
| `--bot-protection` | Enable captcha validation on registration and password-reset forms. |
| `--publish-routes` | Publish route files for application ownership. |
| `--publish-views` | Publish Blade views for application customization. |
| `--force` | Overwrite existing published files. |

Supported social providers are `google`, `facebook`, `twitter`, `linkedin`, and `paypal`.

In interactive mode, the installer asks which auth provider should receive authentication traits immediately after the frontend stack, then asks `Which authentication feature would you like to enable?` and shows a description for every choice. This includes API authentication, which is selected by default and publishes the Sanctum migration. Social login opens a second multi-select for its providers with Google selected by default; enable only providers you plan to configure. The installer reads the `eloquent` providers from `config/auth.php` and applies traits to the selected provider's model when API authentication or passkeys are enabled. In non-interactive mode, only the base web features are enabled unless optional feature flags are supplied; use `--model=<class>` when needed.

The selected model receives `Laravel\Sanctum\HasApiTokens` when API authentication is enabled. When passkeys are enabled, it receives the `Laravel\Fortify\Contracts\PasskeyUser` interface and Auth Kit's `Simtabi\Laranail\Auth\PasskeyAuthenticatable` trait. The model source file must be writable.

When passkeys are enabled, the installer adds `@laravel/passkeys` to `package.json`, copies the passkey browser adapter to `resources/js/passkeys.js`, and imports it from `resources/js/app.js`. Run `npm install` and rebuild your Vite assets after installation. The adapter binds the preset's login, registration, and deletion buttons to Fortify's canonical passkey endpoints; it does not reimplement WebAuthn.

## Configuration

The installer publishes both configuration files:

- `config/auth-kit.php` contains backend authentication, Fortify, and social settings.
- `config/auth-preset.php` controls the frontend stack, bot-protection provider, enabled features, route prefixes, middleware, guard, and redirects.

Enable or disable preset features in `config/auth-preset.php`:

```php
'features' => [
    Features::login(),
    Features::registration(),
    Features::logout(),
    Features::passwordReset(),
    Features::emailVerification(),
],
```

Bot protection is disabled by default. When enabled with `Features::botProtection()`, the preset uses `laranail/captcha` and defaults to Turnstile. Credentials always resolve from configuration, never the database:

```env
CAPTCHA_PROVIDER=turnstile
CAPTCHA_SITE_KEY=
CAPTCHA_SECRET_KEY=
```

Set `CAPTCHA_PROVIDER` to any provider supported by `laranail/captcha`; the Blade markup and validation remain unchanged. Bot protection applies only to the web registration, forgot-password, and reset-password submissions. Login and API requests are not challenged.

### Passkey frontend

Passkey support requires both Fortify's server-side routes and the official browser client. Enabling passkeys with the installer performs the frontend wiring automatically:

```bash
php artisan laranail:authkit.install --passkeys --model='App\\Models\\User'
npm install
npm run build
```

The generated `resources/js/passkeys.js` uses `@laravel/passkeys` for login, registration, and credential deletion. Keep `resources/js/app.js` in the Vite input list; the preset's Blade layout loads that bundle when the application has a Vite manifest or development server.

Route prefixes and redirects can also be customized through `config/auth-preset.php` or its environment variables:

```env
AUTH_PRESET_WEB_PREFIX=auth
AUTH_PRESET_API_PREFIX=api/auth
AUTH_PRESET_GUARD=web
AUTH_PRESET_AFTER_LOGIN=/dashboard
AUTH_PRESET_AFTER_REGISTRATION=/dashboard
```

## Publish resources independently

The preset and Auth Kit expose separate Laravel publish tags. Publish only the resources your application needs instead of running the installer.

### Configuration

```bash
php artisan vendor:publish --tag=auth-kit-config
php artisan vendor:publish --tag=auth-preset-config
```

Use `--force` to overwrite an existing published file:

```bash
php artisan vendor:publish --tag=auth-preset-config --force
```

### Migrations

The preset does not add a migration of its own. Auth Kit provides optional migrations for social accounts and passkeys:

```bash
php artisan vendor:publish --tag=auth-kit-social-migrations
php artisan vendor:publish --tag=auth-kit-passkey-migrations
php artisan migrate
```

When API authentication is enabled, publish Sanctum's migration as well:

```bash
php artisan vendor:publish --tag=sanctum-migrations
php artisan migrate
```

Only publish the migration groups for features enabled in `config/auth-preset.php`. These migrations are published to the application's `database/migrations` directory because their tables belong to the application's database. If the selected model lives in a module, the model location does not alter the schema; move the published files into the module's migration directory only when that module owns and loads its migrations.

### Routes

Publish the web and API route files to `routes/auth-preset-web.php` and `routes/auth-preset-api.php`:

```bash
php artisan vendor:publish --tag=auth-preset-routes
```

Set the route mode to `published` so the package stops loading its bundled route files, then register the published files from the application's route bootstrap:

```php
// config/auth-preset.php
'routes' => [
    'mode' => 'published',
],
```

Require the files from the application's route-loading entry point:

```php
require base_path('routes/auth-preset-web.php');
require base_path('routes/auth-preset-api.php');
```

### Blade views

Publish the views to `resources/views/vendor/auth-preset`:

```bash
php artisan vendor:publish --tag=auth-preset-views
```

The published page views can be edited without modifying the package. They include the login, registration, password, profile, email-verification, and passkey views. The preset's reusable components continue to be loaded from the package namespace.

## Routes and views without publishing

For the standard setup, leave `auth-preset.routes.mode` as `package`. The service provider loads the package routes automatically and Fortify uses the preset's Blade views. Publish resources only when the application needs to own and customize them.

## Testing

From the package directory:

```bash
composer test
composer lint
```

## License

MIT licensed.