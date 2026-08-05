<x-auth-preset::layout title="Register">
    <h2 class="title">Create an account</h2>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="field">
            <x-auth-preset::input id="name" type="text" name="name" :value="old('name')" required autofocus placeholder="Name" />
        </div>

        <div class="field">
            <x-auth-preset::input id="email" type="email" name="email" :value="old('email')" required placeholder="Email address" />
        </div>

        <div class="field">
            <x-auth-preset::input id="password" type="password" name="password" required placeholder="Password" />
        </div>

        <div class="field">
            <x-auth-preset::input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirm password" />
        </div>

        <button type="submit" class="btn">Register</button>
    </form>

    @if (\Simtabi\Laranail\AuthPreset\Features::enabled(\Simtabi\Laranail\AuthPreset\Features::login()))
        <p class="links"><a href="{{ route('login') }}">Already registered?</a></p>
    @endif
</x-auth-preset::layout>
