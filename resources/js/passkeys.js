import { Passkeys } from '@laravel/passkeys';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const showError = (element, error) => {
    if (!element) {
        return;
    }

    element.textContent = error?.message ?? 'Passkey operation failed. Please try again.';
    element.hidden = false;
};

const handleSuccess = (response) => {
    if (response?.redirect) {
        window.location.assign(response.redirect);

        return;
    }

    window.location.reload();
};

const initializeLogin = (element) => {
    const button = element.querySelector('[data-passkey-login-button]');
    const error = element.querySelector('[data-passkey-error]');
    const routes = {
        options: element.dataset.passkeyLoginOptionsUrl,
        submit: element.dataset.passkeyLoginUrl,
    };
    const remember = () => document.querySelector('#remember')?.checked ?? false;

    if (!Passkeys.isSupported()) {
        element.hidden = true;

        return;
    }

    button?.addEventListener('click', async () => {
        if (error) {
            error.hidden = true;
        }

        button.disabled = true;

        try {
            handleSuccess(await Passkeys.verify({ remember, routes }));
        } catch (exception) {
            showError(error, exception);
            button.disabled = false;
        }
    });

    if (!Passkeys.isAutofillSupported()) {
        return;
    }

    Passkeys.autofill({ remember, routes }).catch((exception) => {
        if (exception?.name !== 'AbortError') {
            showError(error, exception);
        }
    });
};

const initializeManagement = (element) => {
    const registerButton = element.querySelector('[data-passkey-register]');
    const registerError = element.querySelector('[data-passkey-register-error]');
    const passwordConfirmation = element.querySelector('[data-password-confirmation]');
    const passwordConfirmationError = element.querySelector('[data-password-confirmation-error]');
    const passwordInput = element.querySelector('[data-password-confirmation-input]');
    const routes = {
        options: element.dataset.passkeyRegistrationOptionsUrl,
        submit: element.dataset.passkeyRegistrationUrl,
    };

    const confirmPassword = async () => {
        const status = await fetch(element.dataset.passwordConfirmationStatusUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (status.ok && (await status.json()).confirmed) {
            return;
        }

        passwordConfirmation.hidden = false;
        passwordConfirmationError.hidden = true;

        if (!passwordInput.value) {
            passwordInput.focus();

            throw new Error('Confirm your password before changing your passkeys.');
        }

        const response = await fetch(element.dataset.passwordConfirmationUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ password: passwordInput.value }),
        });

        if (response.ok) {
            passwordInput.value = '';

            return;
        }

        const payload = await response.json().catch(() => null);
        const message = payload?.errors?.password?.[0] ?? 'Password confirmation failed. Please try again.';

        showError(passwordConfirmationError, new Error(message));
        passwordInput.focus();

        throw new Error(message);
    };

    if (!Passkeys.isSupported()) {
        registerButton?.setAttribute('hidden', 'hidden');
    }

    registerButton?.addEventListener('click', async () => {
        if (registerError) {
            registerError.hidden = true;
        }

        registerButton.disabled = true;

        try {
            await confirmPassword();
            const name = element.querySelector('[data-passkey-name]')?.value?.trim();

            handleSuccess(await Passkeys.register({ name, routes }));
        } catch (exception) {
            showError(registerError, exception);
            registerButton.disabled = false;
        }
    });

    element.querySelectorAll('[data-passkey-delete]').forEach((button) => {
        button.addEventListener('click', async () => {
            const deleteError = element.querySelector('[data-passkey-delete-error]');
            button.disabled = true;

            try {
                await confirmPassword();
                const response = await fetch(button.dataset.passkeyDeleteUrl, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Passkey could not be removed. Please try again.');
                }

                window.location.reload();
            } catch (exception) {
                showError(deleteError, exception);
                button.disabled = false;
            }
        });
    });
};

const initializePasskeys = () => {
    document.querySelectorAll('[data-passkey-login]').forEach(initializeLogin);
    document.querySelectorAll('[data-passkey-management]').forEach(initializeManagement);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePasskeys);
} else {
    initializePasskeys();
}