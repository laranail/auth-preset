@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm/6 font-medium text-gray-900']) }}>
    {{ $value ?? $slot }}
</label>
