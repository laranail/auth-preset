<x-auth-preset::layout title="Login">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Sign in to your account</h2>
        <p class="mt-2 text-sm text-gray-600">
            Or
            @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::registration()))
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    create a new account
                </a>
            @endif
        </p>
    </div>

    <x-auth-preset::validation-errors :errors="$errors" />

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
            <x-auth-preset::label for="password" value="Password" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="password"
                    name="password"
                    type="password"
                    required
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
                    {{ old('remember') ? 'checked' : '' }}
                    class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                >
                <label for="remember" class="ml-3 block text-sm text-gray-700">Remember me</label>
            </div>
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
</x-auth-preset::layout>
