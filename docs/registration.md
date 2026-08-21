# Registration

With package routes enabled, the preset supplies Blade pages and Fortify-backed form handlers under `/auth` by default:

| Flow         | Method and path                 | Access | Result                                                                                      |
|--------------|---------------------------------|--------|---------------------------------------------------------------------------------------------|
| Registration | `GET` / `POST` `/auth/register` | Guest  | Creates a user and redirects to `AUTH_PRESET_AFTER_REGISTRATION` (`/dashboard` by default). |

`POST /auth/register` is limited to ten requests per minute and, when bot protection is enabled, is also validated by the preset CAPTCHA middleware. It redirects successful registrations to `AUTH_PRESET_AFTER_REGISTRATION` (`/dashboard` by default); the selected guard must be able to authenticate the chosen user model. `Features::registration()` determines whether the route, the Fortify registration view, and registration links in the supplied login view are registered:

```php
'features' => [
    Features::registration(),
],
```

Removing the feature removes its preset routes. Remove links from published views and ensure application middleware does not expect a route that is no longer present. Set the guard with `AUTH_PRESET_GUARD`; it must match the guard used by the intended user provider.

The supplied form submits `name`, `email`, `password`, and `password_confirmation`. Auth Kit requires a unique email, lowercases it before storage, and applies Laravel's default password rules. If the selected user model requires verified email, ensure mail and [email verification](email-verification.md) are configured before allowing users into verification-protected application pages.

Registration is not an API endpoint by itself: `POST /api/auth/register` additionally requires `Features::api()` and returns a Sanctum token instead of redirecting. Publish views to change labels, layout, fields, or navigation; a published guest form must retain CSRF and CAPTCHA handling when applicable. For headless behavior, see Auth Kit's [registration guide](../../laranail-auth-kit/docs/registration.md).