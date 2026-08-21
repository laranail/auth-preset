# API routes

Enable the API feature and select the authenticatable model:

```bash
php artisan laranail:authkit.install --api --model='App\Models\User'
php artisan migrate
```

The installer adds Sanctum's `HasApiTokens` trait and publishes the `personal_access_tokens` migration. API routes are registered only when `Features::api()` is enabled; their default prefix is `/api/auth` and middleware is `api` plus `throttle:60,1`. The API routes do not render preset Blade views, create a browser session, run the preset CAPTCHA middleware, or replace a client application's authorization policy.

## Endpoints

| Method and path                                  | Feature            | Protection                             | Result                                                                                 |
|--------------------------------------------------|--------------------|----------------------------------------|----------------------------------------------------------------------------------------|
| `POST /api/auth/register`                        | Registration       | Guest; `throttle:10,1`                 | `201` with `status`, `data.token`, and `data.user`.                                    |
| `POST /api/auth/login`                           | Login              | Guest; `throttle:10,1`                 | `200` with token and user; invalid credentials return `422`, throttling returns `429`. |
| `POST /api/auth/logout`                          | Logout             | `auth:sanctum`                         | Deletes the current access token.                                                      |
| `POST /api/auth/email/verification-notification` | Email verification | `auth:sanctum`, `throttle:6,1`         | Sends a verification notification.                                                     |
| `GET /api/auth/email/verify/{id}/{hash}`         | Email verification | `auth:sanctum`, signed, `throttle:6,1` | Completes verification.                                                                |
| `POST /api/auth/forgot-password`                 | Password reset     | Guest; `throttle:10,1`                 | Sends a reset link through Laravel's password broker.                                  |
| `POST /api/auth/reset-password`                  | Password reset     | Guest; `throttle:10,1`                 | Validates the token and resets the password.                                           |
| `PUT /api/auth/user/password`                    | Password updates   | `auth:sanctum`                         | Uses Auth Kit's password-update action.                                                |
| `PUT /api/auth/user/profile-information`         | Profile management | `auth:sanctum`                         | Uses Auth Kit's profile-update action.                                                 |

Each route exists only when both `Features::api()` and its named feature are enabled. For example, enabling API without login removes `POST /api/auth/login`; enabling login without API leaves only the web flow. `POST /register` and `POST /login` have both the API group's `throttle:60,1` and their endpoint `throttle:10,1` middleware. Authentication failures from the login action return `422`; rate-limit responses return `429`.

Send an issued token as `Authorization: Bearer <token>`. A plaintext token is returned only at registration or login; store it using the client platform's secure mechanism, never log it, and remove it when logout succeeds. The package uses fixed token names (`api-register` and `api-login`) without custom abilities; publish routes/controllers if client-specific abilities, token names, validation, or CAPTCHA/anti-abuse policy are required.

Change the prefix with `AUTH_PRESET_API_PREFIX` and adjust `auth-preset.middleware.api` for application-wide API policy. Passkey ceremonies remain browser/session flows, not API token authentication. Web URLs and rendered views are documented in each feature guide and [route configuration](route-configuration.md).