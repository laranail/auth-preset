# Password reset

Enable this flow with `--password-reset` or `Features::passwordReset()`. Guest routes use the web prefix, `/auth` by default:

| Purpose          | Method and path                        | Protection                                  |
|------------------|----------------------------------------|---------------------------------------------|
| Request a link   | `GET` / `POST` `/auth/forgot-password` | POST is limited to ten requests per minute. |
| Show reset form  | `GET` `/auth/reset-password/{token}`   | Token comes from Laravel's password broker. |
| Set new password | `POST` `/auth/reset-password`          | Limited to ten requests per minute.         |

`POST /auth/forgot-password` and `POST /auth/reset-password` also receive CAPTCHA validation when bot protection is enabled. The supplied login page exposes the forgot-password link only when this feature is active; the provider registers Fortify's request and reset views at the same time. A published form must retain its CSRF token and, when enabled, the `captcha` response.

Configure Laravel's mail transport, password broker, and the public application URL before enabling the form. The broker creates the reset link, and the reset handler validates its token, requires a confirmed password, hashes it, clears the remember token, and revokes personal access tokens when the user model has a `tokens()` relation. Never replace broker token validation with an application-controlled raw request.

For non-browser clients, `POST /api/auth/forgot-password` and `POST /api/auth/reset-password` require both the password-reset and API features, are rate-limited, and return JSON; see [API routes](api-routes.md). Removing password reset removes all four web routes, the matching Fortify views, and both API endpoints when present.
