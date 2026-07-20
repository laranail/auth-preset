<div>
    <form wire:submit="login">
        <div class="field">
            <label for="email">Email</label>
            <input
                wire:model="email"
                type="email"
                id="email"
                name="email"
                required
                autofocus
                placeholder="Email address"
            />
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input
                wire:model="password"
                type="password"
                id="password"
                name="password"
                required
                placeholder="Password"
            />
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field check">
            <input wire:model="remember" type="checkbox" id="remember" />
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>
</div>
