# Installation

Laranail packages are temporarily installed from their Git repositories rather than Packagist. Add the VCS repositories listed in the [README](../README.md#installation) to the consuming application's `composer.json`, then install and run the Blade installer:

```bash
composer require laranail/auth-preset
php artisan laranail:authkit.install
```

Blade is the only supported stack. The interactive installer asks for the authentication provider/model, presents all features as selected by default, and asks which social providers to enable. It publishes `config/auth-kit.php` and `config/auth-preset.php`, configures Tailwind to scan the package views, and leaves package routes enabled by default. Review the published feature list before deploying: a feature controls whether its route and preset UI are registered, not merely whether a button is visible.

## Automated installation

Use explicit options in CI or provisioning scripts:

```bash
php artisan laranail:authkit.install \
    --password-reset \
    --email-verification \
    --api \
    --passkeys \
    --model='App\Models\User' \
    --bot-protection \
    --social=google \
    --publish-views
```

The normal login, registration, logout, profile, and password-update features remain enabled for a non-interactive installation. Add each optional feature flag deliberately; repeat `--social` for every provider. `--publish-routes` and `--publish-views` give the application ownership of those resources instead of requiring a package fork. If routes are published, set `AUTH_PRESET_ROUTES_MODE=published` and load them from the application route bootstrap as described in [route configuration](route-configuration.md).

## Complete the selected setup

| Selection      | Installer action                                                                                                    | Required follow-up                                                                                                                          |
|----------------|---------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| Social         | Publishes Auth Kit's social migration and records enabled providers.                                                | Configure OAuth credentials, run `php artisan migrate`, and see [Social login](social-login.md).                                            |
| API            | Adds Sanctum's `HasApiTokens` trait to the selected model and publishes its migration.                              | Run `php artisan migrate`; see [API routes](api-routes.md).                                                                                  |
| Passkeys       | Adds the Auth Kit passkey trait/interface, publishes its migration, and adds `@laravel/passkeys` plus browser code. | Run `npm install`, build assets, migrate, and see [Passkeys](passkeys.md).                                                                  |
| Bot protection | Enables the feature in preset configuration.                                                                        | Set the selected captcha provider's environment variables; see [Bot protection](bot-protection.md).                                           |

## Verify the installation

After migration and asset work, inspect `config/auth-preset.php` for the selected guard, features, prefixes, middleware, redirects, social providers, and CAPTCHA provider. Run `php artisan route:list` and confirm that only intended `/auth` and `/api/auth` routes exist. Visit `/auth/register` or `/auth/login`; for API installations, obtain a token through the documented register/login endpoint and exercise a protected request. Review the generated configuration, mail delivery, and CAPTCHA behavior before exposing routes outside a local environment.