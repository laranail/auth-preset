<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Attributes\Description;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;

enum AuthenticationFeature: string implements Enumerator
{
    use HasEnumerator;

    #[Label('Login')]
    #[Description('Adds the web login form and authentication endpoint.')]
    case LOGIN = 'login';

    #[Label('Registration')]
    #[Description('Adds the web registration form and account creation endpoint.')]
    case REGISTRATION = 'registration';

    #[Label('Logout')]
    #[Description('Adds the web logout endpoint for authenticated users.')]
    case LOGOUT = 'logout';

    #[Label('Profile information updates')]
    #[Description('Adds the authenticated profile information form and endpoint.')]
    case UPDATE_PROFILE_INFORMATION = 'update-profile-information';

    #[Label('Password updates')]
    #[Description('Adds the authenticated password update form and endpoint.')]
    case UPDATE_PASSWORDS = 'update-passwords';

    #[Label('Social login')]
    #[Description('Adds OAuth callback routes for the providers selected next.')]
    case SOCIAL = 'social';

    #[Label('API authentication')]
    #[Description('Adds Sanctum token authentication routes and publishes its migration.')]
    case API = 'api';

    #[Label('Password reset')]
    #[Description('Adds forgot-password and reset-password views and routes.')]
    case PASSWORD_RESET = 'password-reset';

    #[Label('Email verification')]
    #[Description('Sends and verifies registration email addresses through Fortify.')]
    case EMAIL_VERIFICATION = 'email-verification';

    #[Label('Passkey authentication')]
    #[Description('Adds passkey routes, migration, and the official browser client.')]
    case PASSKEYS = 'passkeys';

    #[Label('Bot protection')]
    #[Description('Protects guest forms with the configured captcha provider.')]
    case BOT_PROTECTION = 'bot-protection';
}
