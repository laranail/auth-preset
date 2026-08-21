<x-auth-preset::layout title="Passkeys">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Manage your passkeys</h2>
        <p class="mt-2 text-sm text-gray-600">Use a passkey to sign in without a password.</p>
    </div>

    @if (session('status') === 'passkey-registered')
        <p class="mb-6 rounded-md bg-green-50 p-3 text-sm text-green-700">Passkey registered successfully.</p>
    @endif

    <div
        data-passkey-management
        data-passkey-registration-options-url="{{ route('passkey.registration-options') }}"
        data-passkey-registration-url="{{ route('passkey.store') }}"
        data-passkey-delete-url-template="{{ route('passkey.destroy', ['passkey' => '__PASSKEY__']) }}"
        data-password-confirmation-url="{{ route('password.confirm.store') }}"
    >
        <div class="mb-8 rounded-md border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-900">Add a passkey</h3>
            <p class="mt-2 text-sm text-gray-600">Choose a name and confirm your password to register a new passkey.</p>
            <label for="passkey-name" class="mt-4 block text-sm font-medium text-gray-700">Passkey name</label>
            <input
                id="passkey-name"
                name="name"
                type="text"
                value=""
                class="mt-2 block w-full rounded-md border-gray-300"
                placeholder="MacBook Pro"
                data-passkey-name
            >
            <label for="passkey-password" class="mt-4 block text-sm font-medium text-gray-700">Current password</label>
            <input
                id="passkey-password"
                name="password"
                type="password"
                autocomplete="current-password"
                class="mt-2 block w-full rounded-md border-gray-300"
                data-passkey-registration-password
            >
            <button
                type="button"
                class="mt-4 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                data-passkey-register
            >
                Register a passkey
            </button>
            <p class="mt-3 text-sm text-red-600" data-passkey-register-error hidden></p>
        </div>

        <h3 class="text-lg font-semibold text-gray-900">Registered passkeys</h3>

        @if ($passkeys->isEmpty())
            <p class="mt-3 text-sm text-gray-600" data-passkey-empty>No passkeys registered yet.</p>
        @else
            <ul class="mt-3 divide-y divide-gray-200" data-passkey-list>
                @foreach ($passkeys as $passkey)
                    <li class="flex items-center justify-between py-4" data-passkey-id="{{ $passkey->id }}">
                        <div>
                            <p class="font-medium text-gray-900">{{ $passkey->name }}</p>
                            <p class="text-sm text-gray-500">Added {{ $passkey->created_at?->toFormattedDateString() }}</p>
                        </div>
                        <button
                            type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-500"
                            data-passkey-delete
                            data-passkey-id="{{ $passkey->id }}"
                            data-passkey-delete-url="{{ route('passkey.destroy', ['passkey' => $passkey]) }}"
                        >
                            Remove
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <dialog class="w-full max-w-md rounded-md border border-gray-200 p-6 text-gray-900 backdrop:bg-gray-900/40" data-passkey-delete-confirmation>
            <h3 class="text-lg font-semibold">Remove this passkey?</h3>
            <p class="mt-2 text-sm text-gray-600">Confirm your password to remove this passkey from your account.</p>
            <label for="passkey-delete-password" class="mt-4 block text-sm font-medium text-gray-700">Current password</label>
            <input
                id="passkey-delete-password"
                name="password"
                type="password"
                autocomplete="current-password"
                class="mt-2 block w-full rounded-md border-gray-300"
                data-passkey-delete-password
            >
            <p class="mt-3 text-sm text-red-600" data-passkey-delete-confirmation-error hidden></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" data-passkey-delete-cancel>Cancel</button>
                <button type="button" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500" data-passkey-delete-confirm>Remove passkey</button>
            </div>
        </dialog>
    </div>
</x-auth-preset::layout>