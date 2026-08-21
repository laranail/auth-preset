<x-auth-preset::dashboard-layout title="Update profile information">
    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white px-8 py-10 sm:rounded-lg">
                @php($profileErrors = $errors->getBag('updateProfileInformation'))
                <div class="mb-8">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Update your profile</h1>
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
                            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Update profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-preset::dashboard-layout>