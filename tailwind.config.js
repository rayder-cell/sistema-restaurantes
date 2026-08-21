/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/assets/**/*.css",  // ← agrega esto
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#fef2f2',
                    100: '#fee2e2',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    900: '#7f1d1d',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};