import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#faf5ff',
                    100: '#f3e8ff',
                    200: '#e9d5ff',
                    300: '#d8b4fe',
                    400: '#c084fc',
                    500: '#a855f7',
                    600: '#9333ea',
                    700: '#7e22ce',
                    800: '#6b21a8',
                    900: '#581c87',
                    950: '#3b0764',
                },
                ink: {
                    50: '#f7f7f9',
                    100: '#ececf1',
                    200: '#cfcfd9',
                    300: '#a3a3b3',
                    400: '#71718a',
                    500: '#4f4f66',
                    600: '#3a3a4f',
                    700: '#2a2a3c',
                    800: '#1a1a26',
                    900: '#0f0f17',
                    950: '#08080d',
                },
            },
            boxShadow: {
                glow: '0 0 0 1px rgb(147 51 234 / 0.05), 0 8px 24px -4px rgb(147 51 234 / 0.12)',
                card: '0 1px 2px rgb(15 15 23 / 0.04), 0 8px 24px -8px rgb(15 15 23 / 0.08)',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: 0, transform: 'translateY(4px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                },
                'scale-in': {
                    '0%': { opacity: 0, transform: 'scale(0.96)' },
                    '100%': { opacity: 1, transform: 'scale(1)' },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.35s ease-out both',
                'scale-in': 'scale-in 0.2s ease-out both',
            },
        },
    },

    plugins: [forms],
};
