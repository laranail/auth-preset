# Social login

Enable only providers that have credentials and an approved callback configured:

```bash
php artisan laranail:authkit.install --social=google --social=linkedin
```

Supported provider keys are `google`, `facebook`, `twitter`, `linkedin`, and `paypal`. The installer adds selected keys to `auth-preset.social.providers`, publishes Auth Kit's social migration, and enables the social feature. Run the migration before exposing the buttons:

```bash
php artisan migrate
```

## Provider setup and routes

Create an OAuth application with each provider and add the exact callback URL that corresponds to the preset's web prefix:

```text
https://your-app.test/auth/social/google/callback
```

Set the provider's `AUTH_KIT_<PROVIDER>_CLIENT_ID`, `CLIENT_SECRET`, and `REDIRECT` values in the application environment, then clear the configuration cache. A configured provider appears only when it is both in `auth-preset.social.providers` and has a client ID. This avoids rendering a button that cannot complete its flow. The social-button component is included in the login Blade view; publishing the view is required to move or restyle it.

With the social feature enabled, guests use `GET /auth/social/{provider}` (`social.redirect`) to start the provider redirect and `GET /auth/social/{provider}/callback` (`social.callback`) to complete it. Both use the web middleware and `guest:<AUTH_PRESET_GUARD>`. The callback creates a session using that guard and redirects to `AUTH_PRESET_AFTER_SOCIAL_LOGIN`, `/dashboard` by default. Neither route is created by `Features::api()`, and the preset has no API social-token exchange.

## Account-linking safety

The preset delegates identity handling to Auth Kit. Existing provider identities are reused; an already authenticated user may link a provider identity. For guests, a matching email is auto-linked only when the provider supplies a trusted verified-email claim. Google, LinkedIn, and PayPal are trusted for that purpose; Facebook and X/Twitter are not. Missing or unverified email claims do not silently link an existing local account.

The migration stores access and refresh tokens, so treat the `socials` table as sensitive data and do not serialize it in APIs. For scopes, PayPal sandbox configuration, custom providers, and the complete identity-resolution rules, read Auth Kit's [social login guide](../../laranail-auth-kit/docs/social-login.md).