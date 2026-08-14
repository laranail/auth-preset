<x-auth-preset::layout title="Reset Password">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Set a new password</h2>
    </div>

    <x-auth-preset::validation-errors :errors="$errors" />

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-auth-preset::label for="email" value="Email address" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email', $request->email)"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                    :error="$errors->has('email')"
                />
            </div>
            <x-auth-preset::input-error :message="$errors->first('email')" />
        </div>

        <div>
            <x-auth-preset::label for="password" value="New password" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Enter your new password"
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
                    placeholder="Confirm your new password"
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
                Reset password
            </button>
        </div>
    </form>
</x-auth-preset::layout>
