# Security

The preset supplies authentication routes, not application authorization. Use HTTPS, secure session cookies, CSRF protection, trusted hosts/proxies, a working mail transport, and application middleware such as `auth`, `verified`, or Sanctum abilities on protected application pages. The package `/dashboard` demonstrates an authenticated route only; it is not a safe authorization boundary for an application.

## Package controls

- `AUTH_PRESET_GUARD` must identify the intended user provider; verify the provider/model relationship before enabling registration, session login, or profile changes.
- Guest web login, registration, and password-reset submissions have `throttle:10,1`; verification actions use `throttle:6,1`; the API group defaults to `throttle:60,1`. Auth Kit adds a separate credential limiter for login.
- `Features::botProtection()` validates CAPTCHA server-side on guest web submissions, but not API routes. See [bot protection](bot-protection.md).
- Successful web login regenerates the session; web logout invalidates it and regenerates the CSRF token. Password resets and updates revoke personal tokens when the model supports them.
- Social credentials and the migrated social-token table are sensitive. Enable only configured providers and follow the verified-email linking rules in [social login](social-login.md).

## Deployment review

Set the correct web/API prefixes before registering OAuth callbacks, and check the actual routes and middleware after every feature or route-mode change. Treat bearer tokens as secrets: return them only over TLS, do not log them, and revoke them with the API logout route. Confirm the public host, signed URLs, and mail links for password reset and verification. Passkeys require a matching relying-party ID, origin, and TLS configuration.

Review Auth Kit's [security guidance](../../laranail-auth-kit/docs/security.md) for the underlying action behavior, social linking, token revocation, and passkey requirements.