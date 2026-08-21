<x-auth-preset::layout title="Verify Email">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Verify your email</h2>
        <p class="mt-2 text-sm text-gray-600">
            We've sent a verification link to your email address. Please click the link to verify your account.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-indigo-600 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
                Resend verification email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="text-sm font-semibold text-gray-600 hover:text-gray-500"
            >
                Log out
            </button>
        </form>
    </div>
</x-auth-preset::layout>
