# Login

Login has two deliberately separate interfaces. The Blade flow creates a session for the configured guard; the API flow issues a Sanctum personal-access token. Enabling one does not make the other available.

## Web login

When package routes are enabled and `Features::login()` is present, the preset registers these guest routes under `AUTH_PRESET_WEB_PREFIX` (`auth` by default):

| Route                              | Middleware and result                                                                                                                                                                                                    |
|------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `GET /auth/login` (`login`)        | `web` and `guest:<AUTH_PRESET_GUARD>`; renders `auth-preset::blade.login`.                                                                                                                                               |
| `POST /auth/login` (`login.store`) | The same guest middleware, `throttle:10,1`, and CAPTCHA validation when bot protection is enabled. A successful attempt creates a session and redirects to the intended URL or `AUTH_PRESET_AFTER_LOGIN` (`/dashboard`). |

The supplied form posts `email`, `password`, and optional `remember`. It keeps the email and remember choice on an invalid-credential response, attaches the error to `email`, and returns `429` after an Auth Kit credential throttle. Successful login regenerates the session. Auth Kit also applies its credential limiter, keyed by the configured guard, lowercased email, and client IP; that limit is configured independently from the route's ten-per-minute limit.

The Blade page conditionally shows a registration link, forgot-password link, social buttons, CAPTCHA widget, and passkey button according to their individual feature/configuration state. It does not render a token or call an API endpoint. Publish the views to change labels, layout, links, or fields; preserve the route names unless all corresponding links are changed too.

## API login

`POST /api/auth/login` exists only when **both** `Features::api()` and `Features::login()` are enabled. It uses the API middleware group (by default `api` and `throttle:60,1`) plus `throttle:10,1`, accepts the same credential fields, and returns JSON rather than starting a web session:

| Outcome                           | Response                                                                                    |
|-----------------------------------|---------------------------------------------------------------------------------------------|
| Valid credentials                 | `200` with `status: success`, `data.token`, and `data.user`; the token name is `api-login`. |
| Invalid credentials               | `422` with `status: failed`.                                                                |
| Auth Kit credential limit reached | `429` with `status: throttled` and `data.retry_after`.                                      |

CAPTCHA middleware is attached to the named **web** guest submissions only; it does not protect API login. Store the plaintext token only in the client platform's secure storage, never logs, and send it on protected API requests as `Authorization: Bearer <token>`. See [API routes](api-routes.md) for the full token surface.

## Enabling and disabling

Remove `Features::login()` from `auth-preset.features` to remove both preset login routes, the registered Fortify login view, and login-dependent UI. Remove `Features::api()` to keep the Blade flow while removing every API route. Changing `AUTH_PRESET_GUARD`, either prefix, route mode, or middleware changes the actual route surface; verify it with `php artisan route:list` after configuration changes. For a headless implementation or custom response hooks, see Auth Kit's [login guide](../../laranail-auth-kit/docs/login.md).