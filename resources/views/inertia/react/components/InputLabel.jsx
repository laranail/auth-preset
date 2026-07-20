export default function InputLabel({ value, className = '', ...props }) {
    return (
        <label
            {...props}
            className={`block text-sm font-medium text-gray-700 ${className}`}
        >
            {value}
        </label>
    );
}
