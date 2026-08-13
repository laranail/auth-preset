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
    >
        <div class="mb-8 rounded-md border border-gray-200 p-4">
            <label for="passkey-name" class="block text-sm font-medium text-gray-700">Passkey name</label>
            <input
                id="passkey-name"
                name="name"
                type="text"
                value=""
                class="mt-2 block w-full rounded-md border-gray-300 shadow-xs"
                placeholder="MacBook Pro"
                data-passkey-name
            >
            <button
                type="button"
                class="mt-4 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500"
                data-passkey-register
            >
                Register a passkey
            </button>
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
    </div>
</x-auth-preset::layout>