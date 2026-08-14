<x-auth-preset::layout title="Forgot Password">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Reset your password</h2>
        <p class="mt-2 text-sm text-gray-600">
            Enter your email and we'll send you a link to reset your password.
        </p>
    </div>

    <x-auth-preset::validation-errors :errors="$errors" />

    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <div>
            <x-auth-preset::label for="email" value="Email address" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                    :error="$errors->has('email')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('email')" />
        </div>

        @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::turnstile()))
            <x-auth-preset::turnstile />
        @endif

        <div>
            <button
                type="submit"
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Send reset link
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                Back to login
            </a>
        </div>
    </form>
</x-auth-preset::layout>
