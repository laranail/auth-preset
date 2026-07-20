export default function InputError({ message, className = '' }) {
    return message ? (
        <p className={`text-sm text-red-600 mt-1 ${className}`}>{message}</p>
    ) : null;
}
