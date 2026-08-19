<x-auth-preset::dashboard-layout title="Dashboard">
    <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h1>
        </div>
    </header>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Welcome back, {{ $user->name }}.
                </div>
            </div>
        </div>
    </div>
</x-auth-preset::dashboard-layout>