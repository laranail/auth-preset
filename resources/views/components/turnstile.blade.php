<script
    src="https://challenges.cloudflare.com/turnstile/v0/api.js"
    defer
></script>

<div
    @error(config('auth-kit.turnstile.input', 'cf-turnstile-response')) aria-invalid="true" @enderror
    class="auth-kit-turnstile"
>
    <div class="auth-kit-turnstile-widget">
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('auth-kit.turnstile.site_key') }}"
            data-size="flexible"
        ></div>
    </div>

    @error(config('auth-kit.turnstile.input', 'cf-turnstile-response'))
        <p class="auth-kit-turnstile-error">{{ $message }}</p>
    @enderror
</div>