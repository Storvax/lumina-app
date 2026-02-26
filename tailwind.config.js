import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    safelist: [
        // Cores dinâmicas usadas nas auto-avaliações (severidade e tipo)
        {
            pattern: /^(bg|text|border)-(teal|amber|orange|rose|red|violet)-(50|100|200|300|500|600|700)$/,
        },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // As tuas cores personalizadas
                primary: {
                    50:  '#f5f3ff',
                    100: '#ede9fe',
                    300: '#a5b4fc',
                    400: '#818cf8', // dark mode hover
                    500: '#6366f1', // Indigo vibrante
                    600: '#4f46e5',
                    700: '#4338ca',
                    900: '#312e81',
                },
                calm: {
                    50: '#f0fdfa',
                    500: '#14b8a6', // Teal
                },
            },
            animation: {
                float: 'float 6s ease-in-out infinite',
                'fade-up': 'fadeUp 0.8s ease-out forwards',
                breathe: 'breathe 4s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
                fadeUp: {
                    from: { opacity: '0', transform: 'translateY(20px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                breathe: {
                    '0%, 100%': { transform: 'scale(1)', opacity: '0.8' },
                    '50%': { transform: 'scale(1.5)', opacity: '0.4' },
                }
            },
        },
    },

    plugins: [forms],
};