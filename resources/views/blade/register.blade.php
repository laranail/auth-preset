<x-auth-preset::layout title="Register">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Create your account</h2>
        <p class="mt-2 text-sm text-gray-600">
            Already have an account?
            @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::login()))
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Sign in
                </a>
            @endif
        </p>
    </div>

    <x-auth-preset::validation-errors :errors="$errors" />

    <x-auth-preset::social-buttons />

    <form method="POST" action="{{ route('register.store') }}" class="space-y-6">
        @csrf

        <div>
            <x-auth-preset::label for="name" value="Full name" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="name"
                    name="name"
                    type="text"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="John Doe"
                    :error="$errors->has('name')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('name')" />
        </div>

        <div>
            <x-auth-preset::label for="email" value="Email address" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                    :error="$errors->has('email')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('email')" />
        </div>

        <div>
            <x-auth-preset::label for="password" value="Password" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="At least 8 characters"
                    :error="$errors->has('password')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('password')" />
        </div>

        <div>
            <x-auth-preset::label for="password_confirmation" value="Confirm password" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Re-enter your password"
                    :error="$errors->has('password_confirmation')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('password_confirmation')" />
        </div>

        @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::turnstile()))
            <x-auth-preset::turnstile />
        @endif

        <div>
            <button
                type="submit"
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Create account
            </button>
        </div>
    </form>
</x-auth-preset::layout>
