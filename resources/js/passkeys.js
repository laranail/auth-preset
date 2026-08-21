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
    const registrationPasswordInput = element.querySelector('[data-passkey-registration-password]');
    const deletionDialog = element.querySelector('[data-passkey-delete-confirmation]');
    const deletionPasswordInput = element.querySelector('[data-passkey-delete-password]');
    const deletionConfirmationError = element.querySelector('[data-passkey-delete-confirmation-error]');
    const deletionConfirmButton = element.querySelector('[data-passkey-delete-confirm]');
    const deletionCancelButton = element.querySelector('[data-passkey-delete-cancel]');
    let passkeyToDelete;
    const routes = {
        options: element.dataset.passkeyRegistrationOptionsUrl,
        submit: element.dataset.passkeyRegistrationUrl,
    };

    const confirmWithPassword = async (input, error) => {
        error.hidden = true;

        if (!input.value) {
            input.focus();

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
            body: JSON.stringify({ password: input.value }),
        });

        if (response.ok) {
            input.value = '';

            return;
        }

        const payload = await response.json().catch(() => null);
        const message = payload?.errors?.password?.[0] ?? 'Password confirmation failed. Please try again.';

        showError(error, new Error(message));
        input.focus();

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
            const name = element.querySelector('[data-passkey-name]')?.value?.trim();

            if (!name) {
                throw new Error('Enter a name for this passkey.');
            }

            await confirmWithPassword(registrationPasswordInput, registerError);

            handleSuccess(await Passkeys.register({ name, routes }));
        } catch (exception) {
            showError(registerError, exception);
            registerButton.disabled = false;
        }
    });

    element.querySelectorAll('[data-passkey-delete]').forEach((button) => {
        button.addEventListener('click', async () => {
            passkeyToDelete = button;
            deletionConfirmationError.hidden = true;
            deletionDialog.showModal();
            deletionPasswordInput.focus();
        });
    });

    deletionCancelButton?.addEventListener('click', () => deletionDialog.close());

    deletionConfirmButton?.addEventListener('click', async () => {
        deletionConfirmButton.disabled = true;

        try {
            await confirmWithPassword(deletionPasswordInput, deletionConfirmationError);
            const response = await fetch(passkeyToDelete.dataset.passkeyDeleteUrl, {
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
            showError(deletionConfirmationError, exception);
            deletionConfirmButton.disabled = false;
        }
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