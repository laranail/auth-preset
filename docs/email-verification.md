# Email verification

Enable verification with `--email-verification` or `Features::emailVerification()`. The user model must implement Laravel's `MustVerifyEmail` contract; otherwise the notification and verified state cannot operate as expected. Configure mail before presenting the verification prompt.

The preset registers these authenticated routes beneath `/auth` by default:

| Purpose              | Method and path                                | Protection                                 |
|----------------------|------------------------------------------------|--------------------------------------------|
| Verification prompt  | `GET` `/auth/email/verify`                     | Configured guard                           |
| Verify a signed link | `GET` `/auth/email/verify/{id}/{hash}`         | Guard, signed URL, six requests per minute |
| Resend notification  | `POST` `/auth/email/verification-notification` | Guard, six requests per minute             |

The Blade prompt directs the user to their inbox and provides the resend action. Laravel's signed URL validation prevents tampering; use the generated notification URL and do not replace it with an unsigned application route. Both the prompt and the resend link disappear from the preset when the feature is disabled, but application-owned `verified` middleware remains the authority for access control.

Add Laravel's `verified` middleware to application routes that require a confirmed email. When a verified user changes their email through the preset profile page, Auth Kit clears `email_verified_at` and sends a new notification, so the application must handle the temporary unverified state. Removing the feature removes its routes and UI but does not make protected application routes safe to access—review their middleware at the same time.

With API enabled, the same feature also registers `POST /api/auth/email/verification-notification` and `GET /api/auth/email/verify/{id}/{hash}`. Both require `auth:sanctum`; the verification URL is additionally signed and both endpoints use `throttle:6,1`. API clients must send the bearer token when following the verification link, which is often unsuitable for email clicks; use the web flow or publish the route if a native-client verification design needs different behavior.

For headless controller behavior and mail integration details, see Auth Kit's [email verification guide](../../laranail-auth-kit/docs/email-verification.md).