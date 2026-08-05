<x-auth-preset::layout title="Login">

    <h2 class="title">Login</h2>

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
        </div>

        <div class="field">
            <x-auth-preset::input
                id="password"
                type="password"
                name="password"
                required
                placeholder="Password"
            />
        </div>

        <div class="field check">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::registration()))
        <p class="links"><a href="{{ route('register') }}">Create an account</a></p>
    @endif
</x-auth-preset::layout>
