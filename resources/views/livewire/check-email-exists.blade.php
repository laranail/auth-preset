<div>
    <input
        wire:model.live="email"
        type="email"
        id="email"
        name="email"
        placeholder="Email address"
    />

    @if ($exists === true)
        <p class="error">This email is already registered.</p>
    @elseif ($exists === false)
        <p style="color: #16a34a; font-size: 0.75rem; margin-top: 0.25rem;">Email is available.</p>
    @endif
</div>
