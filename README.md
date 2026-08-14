# Laravel Auth Preset

Blade authentication scaffolding for Laravel 13+, powered by [`laranail/auth-kit`](https://github.com/laranail/auth-kit).

The preset provides configurable web and API authentication routes, Fortify-backed password and profile flows, Blade views, social login integration, passkeys, and optional Cloudflare Turnstile validation.

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

The installer currently supports the Blade stack. It publishes the Auth Kit and auth preset configuration, then asks which optional authentication features should be enabled. Package routes are registered automatically by default.

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
    --turnstile \
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
| `--passkeys` | Enable passkey authentication and publish its migration. |
| `--turnstile` | Enable Turnstile validation on registration and password-reset forms. |
| `--publish-routes` | Publish route files for application ownership. |
| `--publish-views` | Publish Blade views for application customization. |
| `--force` | Overwrite existing published files. |

Supported social providers are `google`, `facebook`, `twitter`, `linkedin`, and `paypal`.

In interactive mode, optional features are prompted for individually. In non-interactive mode, optional features remain disabled unless their command option is supplied.

## Configuration

The installer publishes both configuration files:

- `config/auth-kit.php` contains backend authentication, Fortify, social, and Turnstile settings.
- `config/auth-preset.php` controls the frontend stack, enabled features, route prefixes, middleware, guard, and redirects.

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

Turnstile is disabled by default. When enabled with `Features::turnstile()`, add the Cloudflare credentials to `.env`:

```env
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
# Optional: override Cloudflare's siteverify endpoint.
TURNSTILE_URL=https://challenges.cloudflare.com/turnstile/v0/siteverify
```

Turnstile applies only to the web registration, forgot-password, and reset-password submissions. Login and API requests are not challenged.

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

Only publish the migration groups for features enabled in `config/auth-preset.php`.

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