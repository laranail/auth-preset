@props(['title' => 'Dashboard'])

<x-auth-preset::layout
    :$title
    body-class="bg-gray-100 font-sans text-gray-900 antialiased"
    main-class="contents"
    content-class="contents"
    card-class="contents"
>
    <nav class="border-b border-gray-100 bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-900">Dashboard</a>
                @if (\Illuminate\Support\Facades\Route::has('user-profile-information.edit'))
                    <a href="{{ route('user-profile-information.edit') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Profile</a>
                @endif
                @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::passkeys()) && \Illuminate\Support\Facades\Route::has('user-passkeys.index'))
                    <a href="{{ route('user-passkeys.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Passkeys</a>
                @endif
            </div>
            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
        </div>
    </nav>

    {{ $slot }}
</x-auth-preset::layout>