@props(['disabled' => false, 'name'])

<input
    {{ $disabled ? 'disabled' : '' }}
    name="{{ $name }}"
    {!! $attributes->merge([
        'class' => 'rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full'
    ]) !!}
>

@error($name)
    <p class="error">{{ $message }}</p>
@enderror
