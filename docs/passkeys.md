# Passkeys

Enable passkeys against the authenticatable model that will own the credentials:

```bash
php artisan laranail:authkit.install --passkeys --model='App\Models\User'
npm install
npm run build
php artisan migrate
```

The installer adds Auth Kit's `PasskeyAuthenticatable` trait and the Fortify interface to the chosen model, publishes the passkeys migration, installs `@laravel/passkeys`, and wires `resources/js/passkeys.js` into the application's Vite entry. Do not skip the asset build: the Blade login and management controls require this browser client.

## User experience

The login page displays a passkey action when this feature is enabled; the browser client tests WebAuthn support and also attempts supported conditional/autofill login. It uses Fortify's canonical passkey options and assertion endpoints, which are separate from `/auth/login` and are registered by Fortify/Passkeys rather than this preset route file. A successful ceremony follows the redirect returned by that endpoint, otherwise the client reloads the page. Errors are rendered in the form rather than exposed as raw browser exceptions.

Authenticated users manage credentials at `GET /auth/user/passkeys` (`user-passkeys.index`). This route uses the configured web middleware and `auth:<AUTH_PRESET_GUARD>`, then the page lists passkeys, registers a named credential, and deletes one through the canonical Fortify endpoints. It is registered only when `Features::passkeys()` is enabled. Passkey management is not exposed through the preset API route file, and passkey login does not issue a Sanctum token.

## Relying-party configuration

Set the relying-party ID and allowed origins in `config/fortify.php`. `APP_URL`, the browser origin, TLS certificate, and relying-party ID must agree; a production credential cannot be used from an unrelated local origin. Keep the Vite entry and CSRF meta tag in the Blade layout, because deletion uses an authenticated CSRF-protected request.

Passkey login creates a web session; it is not a Sanctum API token flow. For migration ownership and WebAuthn implementation requirements, see Auth Kit's [passkey guide](../../laranail-auth-kit/docs/passkeys.md).