@props(['title' => 'Login'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - {{ $title }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background-color: #f9fafb; color: #111827; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; width: 100%; max-width: 400px; }
        .title { font-size: 1.5rem; font-weight: 600; text-align: center; margin-bottom: 1.5rem; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; }
        .field input[type="email"],
        .field input[type="password"],
        .field input[type="text"] { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; outline: none; transition: border-color 0.15s; }
        .field input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .check { display: flex; align-items: center; gap: 0.5rem; }
        .check input[type="checkbox"] { width: 1rem; height: 1rem; }
        .check label { font-size: 0.875rem; color: #6b7280; }
        .btn { width: 100%; padding: 0.625rem 1rem; background: #111827; color: #fff; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: background 0.15s; }
        .btn:hover { background: #1f2937; }
        .error { color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; }
        .links { text-align: center; margin-top: 1rem; font-size: 0.875rem; }
        .links a { color: #3b82f6; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        {{ $slot }}
    </div>
</body>
</html>
