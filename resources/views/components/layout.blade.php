@props([
    'title' => 'Auth',
    'bodyClass' => 'bg-gray-50 font-sans text-gray-900 antialiased',
    'mainClass' => 'flex min-h-screen items-center justify-center p-4',
    'contentClass' => 'w-full max-w-md',
    'cardClass' => 'rounded-xl bg-white px-8 py-10 ring-1 ring-gray-950/5',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - {{ $title }}</title>
    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
</head>
<body class="{{ $bodyClass }}">
<main class="{{ $mainClass }}">
    <div class="{{ $contentClass }}">
        <div class="{{ $cardClass }}">
            {{ $slot }}
        </div>
    </div>
</main>
</body>
</html>
