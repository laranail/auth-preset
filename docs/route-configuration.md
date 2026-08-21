# Route configuration

The service provider loads `routes/web.php` and `routes/api.php` only while `auth-preset.routes.mode` is `package` (the default). Web URLs use `AUTH_PRESET_WEB_PREFIX=auth`; API URLs use `AUTH_PRESET_API_PREFIX=api/auth`. Prefixes do not include a leading slash in configuration, though the resulting URLs do. After changing either one, update frontend links, OAuth provider callback URLs, API client configuration, and tests.

## Route mode

Set `AUTH_PRESET_ROUTES_MODE=published` only after publishing the routes and loading `routes/auth-preset-web.php` and `routes/auth-preset-api.php` from the application's route bootstrap. In published mode the package intentionally loads neither route file; forgetting the application `require` produces no preset routes. This mode is the appropriate place to change controllers, middleware, names, or authorization policy.

## Guards and middleware

Guest web pages use the configured `web` middleware list plus `guest:<AUTH_PRESET_GUARD>`. Logout, profile, password update, passkey management, verification prompt, and verification actions use the same web middleware plus `auth:<guard>`. API routes use `auth-preset.middleware.api` (`api`, `throttle:60,1` by default); protected API account routes add `auth:sanctum`.

The package also registers `GET /dashboard`, independent of the web prefix, with the web middleware and `auth:<guard>`. It is a basic authenticated view and not a substitute for application authorization; replace or remove it when it does not suit the application.

## Feature gates

Every feature gate removes its own routes rather than merely hiding a link. `Features::api()` is a top-level gate for all API routes; each API endpoint needs that gate **and** its own feature. Web route/page gates are: login, registration, logout, password reset, profile updates, password updates, email verification, social login, and passkeys. Disabling a feature after publishing views can leave broken links, so update the view navigation and run `php artisan route:list` for both route modes.

For the exact API surface and response expectations, see [API routes](api-routes.md). To replace route behavior or presentation, see [customization](customization.md).