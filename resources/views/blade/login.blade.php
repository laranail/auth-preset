@extends('auth-preset::layouts.app')

@section('title', 'Login')

@section('content')
    <h2 class="title">Login</h2>

    <x-auth-preset::validation-errors :errors="$errors" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <x-auth-preset::input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                placeholder="Email address"
            />
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <x-auth-preset::input
                id="password"
                type="password"
                name="password"
                required
                placeholder="Password"
            />
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field check">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>
@endsection
