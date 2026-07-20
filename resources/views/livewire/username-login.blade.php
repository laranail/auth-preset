<div>
    <form wire:submit="login">
        <div class="field">
            <label for="username">Username</label>
            <input
                wire:model="username"
                type="text"
                id="username"
                name="username"
                required
                autofocus
                placeholder="Username"
            />
            @error('username')
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
