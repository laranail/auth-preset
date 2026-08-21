# Testing

From the package directory, run:

```bash
composer test
composer lint
```

The package tests cover installation, route registration, resource publishing, bot protection, social callbacks, passkey controllers, API tokens, and profile/password updates. Documentation changes do not require these commands, but application changes to the selected flows should be tested against the installed route surface.

At minimum, cover each enabled web feature for its guest/authenticated middleware, expected redirect or validation error, and feature-disabled absence. Cover CAPTCHA success and failure for every published guest form; route throttling and Auth Kit credential throttling separately; and mail/signed-link behavior for password reset and verification. For API installations, cover token issuance, bearer-token access, invalid credentials, `429` responses, current-token logout, and that disabled feature endpoints return no route.

Test published route/view changes using their configured prefixes and guard. Exercise social callback URLs with a provider test application, and test passkeys on the actual HTTPS host and relying-party ID planned for deployment. Test the production host/origin configuration before releasing passkeys or OAuth.