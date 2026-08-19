<x-auth-preset::layout title="Login">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Sign in to your account</h2>
        <p class="mt-2 text-sm text-gray-600">
            @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::registration()))
                Don't have account?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    register.
                </a>
            @endif
        </p>
    </div>

    <x-auth-preset::social-buttons />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-auth-preset::label for="email" value="Email address" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    autofocus
                    autocomplete="email webauthn"
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
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    :error="$errors->has('password')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('password')" />
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                    class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                >
                <label for="remember" class="ml-3 block text-sm text-gray-700">Remember me</label>
            </div>

            @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::passwordReset()))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                    Forgot password?
                </a>
            @endif
        </div>

        <div>
            <button
                type="submit"
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Sign in
            </button>
        </div>
    </form>

    @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::passkeys()))
        <div
            class="mt-6"
            data-passkey-login
            data-passkey-login-options-url="{{ route('passkey.login-options') }}"
            data-passkey-login-url="{{ route('passkey.login') }}"
        >
            <p class="mb-3 text-sm text-red-600" data-passkey-error hidden></p>
            <button
                type="button"
                class="flex w-full justify-center rounded-md border border-indigo-600 px-3 py-2 text-sm font-semibold text-indigo-600 shadow-xs hover:bg-indigo-50"
                data-passkey-login-button
            >
                Sign in with a passkey
            </button>
        </div>
    @endif
</x-auth-preset::layout>
