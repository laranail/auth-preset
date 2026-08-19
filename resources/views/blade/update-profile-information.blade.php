<x-auth-preset::layout title="Update profile information">
    @php($profileErrors = $errors->getBag('updateProfileInformation'))
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Update your profile</h2>
        <p class="mt-2 text-sm text-gray-600">Keep your account information up to date.</p>
    </div>

    <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <x-auth-preset::label for="name" value="Full name" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="name"
                    name="name"
                    type="text"
                    :value="old('name', $user->name)"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="John Doe"
                    :error="$profileErrors->has('name')"
                />
            </div>
            <x-auth-preset::input-error :message="$profileErrors->first('name')" />
        </div>

        <div>
            <x-auth-preset::label for="email" value="Email address" />
            <div class="mt-2">
                <x-auth-preset::input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email', $user->email)"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                    :error="$profileErrors->has('email')"
                />
            </div>
            <x-auth-preset::input-error :message="$profileErrors->first('email')" />
        </div>

        <div>
            <button
                type="submit"
                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Update profile
            </button>
        </div>
    </form>
</x-auth-preset::layout>