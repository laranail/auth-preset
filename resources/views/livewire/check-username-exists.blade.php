<div>
    <input
        wire:model.live="username"
        type="text"
        id="username"
        name="username"
        placeholder="Username"
    />

    @if ($exists === true)
        <p class="error">This username is already taken.</p>
    @elseif ($exists === false)
        <p style="color: #16a34a; font-size: 0.75rem; margin-top: 0.25rem;">Username is available.</p>
    @endif
</div>
