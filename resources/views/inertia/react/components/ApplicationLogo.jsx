export default function ApplicationLogo({ className = '' }) {
    return (
        <svg
            viewBox="0 0 40 40"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            <path
                d="M2 20a18 18 0 1 1 36 0 18 18 0 0 1-36 0Z"
                fill="currentColor"
                opacity="0.2"
            />
            <path
                d="M20 6a14 14 0 1 0 0 28 14 14 0 0 0 0-28Zm0 4a10 10 0 1 1 0 20 10 10 0 0 1 0-20Z"
                fill="currentColor"
            />
        </svg>
    );
}
