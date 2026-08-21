# Password updates

With `Features::updatePasswords()` enabled, these authenticated web routes are registered under the web prefix:

| Route                                              | Behavior                                                                                     |
|----------------------------------------------------|----------------------------------------------------------------------------------------------|
| `GET /auth/user/password` (`user-password.edit`)   | Renders the supplied password-update page.                                                   |
| `PUT /auth/user/password` (`user-password.update`) | Requires the current password and accepts the replacement through Auth Kit's Fortify action. |

Both use the configured web middleware and `auth:<AUTH_PRESET_GUARD>`. The Blade form must be CSRF-protected and submit the expected current-password, password, and password-confirmation fields. Publish it for presentation changes; change the Auth Kit action as well when adding application-specific password policy or persistence behavior.

A successful change clears the remember token, revokes personal tokens when available, and asks compatible guards to log out other devices. The API equivalent, `PUT /api/auth/user/password`, requires API and password-update features plus `auth:sanctum`; it returns JSON rather than rendering the page. See [API routes](api-routes.md). Removing the feature removes both web routes and the API endpoint, but does not revoke existing sessions or tokens.