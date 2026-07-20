import { forwardRef } from 'react';

const TextInput = forwardRef(function TextInput(
    { type = 'text', className = '', isFocused = false, ...props },
    ref
) {
    return (
        <input
            {...props}
            type={type}
            className={`rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full ${className}`}
            ref={ref}
        />
    );
});

export default TextInput;
