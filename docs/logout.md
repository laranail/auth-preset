# Logout

When `Features::logout()` is enabled, `POST /auth/logout` (`logout`) is registered under the web prefix with the configured web middleware and `auth:<AUTH_PRESET_GUARD>`. It invalidates the current session, regenerates the CSRF token, and redirects to `AUTH_PRESET_AFTER_LOGOUT` (`/` by default). There is no GET logout route.

Use a CSRF-protected form; a link or JavaScript request must supply the token as well. Disabling the feature removes the route, so remove published-view navigation that targets `route('logout')`.

API logout is separate: `POST /api/auth/logout` exists only when API and logout are enabled, requires `auth:sanctum`, and deletes the current bearer token rather than a web session. It does not revoke every token owned by the user. See [API routes](api-routes.md) when the client needs broader token-revocation behavior.