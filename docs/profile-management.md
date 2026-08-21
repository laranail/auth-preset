# Profile management

Profile changes require the configured guard and are under the web prefix. Both routes use the configured web middleware and `auth:<AUTH_PRESET_GUARD>`:

| Flow    | Method and path                                | Behavior                                   |
|---------|------------------------------------------------|--------------------------------------------|
| Profile | `GET` / `PUT` `/auth/user/profile-information` | Updates the name and unique email address. |

Keep `Features::updateProfileInformation()` in the feature list to register the page and handler. The action accepts a required name and unique email. When a user implements `MustVerifyEmail`, changing their email clears its verification state and sends a new notification; application pages protected by `verified` will then deny access until the new address is confirmed.

The API equivalent is `PUT /api/auth/user/profile-information`, which requires both API and profile features plus a Sanctum bearer token; it does not render the Blade page. The supplied Blade page is a starting point only. Publish it to add application-specific profile fields, links, or success messaging, then extend or replace the underlying Auth Kit action when those fields need validation or persistence. See [route configuration](route-configuration.md) and Auth Kit's [profile-management guide](../../laranail-auth-kit/docs/profile-management.md). For password changes, see [password updates](password-updates.md).