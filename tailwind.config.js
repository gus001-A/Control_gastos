import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Morado intenso: color principal del sistema (junto con negro y blanco).
const ACENTO = {
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
};

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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Acento principal (morado). Junto con negro y blanco forma la paleta del sistema.
                brand: ACENTO,
                purple: ACENTO,
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
                glow: '0 0 0 1px rgb(147 51 234 / 0.08), 0 12px 32px -8px rgb(147 51 234 / 0.35)',
                card: '0 1px 2px rgb(15 23 42 / 0.04), 0 1px 3px rgb(15 23 42 / 0.03)',
                md: '0 1px 2px rgb(15 23 42 / 0.04), 0 4px 12px -2px rgb(15 23 42 / 0.08)',
                lg: '0 2px 4px rgb(15 23 42 / 0.04), 0 12px 24px -6px rgb(15 23 42 / 0.10)',
                xl: '0 4px 8px rgb(15 23 42 / 0.04), 0 24px 48px -12px rgb(15 23 42 / 0.16)',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: 0, transform: 'translateY(10px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                },
                'scale-in': {
                    '0%': { opacity: 0, transform: 'scale(0.92)' },
                    '60%': { opacity: 1, transform: 'scale(1.02)' },
                    '100%': { opacity: 1, transform: 'scale(1)' },
                },
                float: {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '33%': { transform: 'translate(30px, -20px) scale(1.08)' },
                    '66%': { transform: 'translate(-20px, 15px) scale(0.95)' },
                },
                'gradient-x': {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                shimmer: {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(100%)' },
                },
                wiggle: {
                    '0%, 100%': { transform: 'rotate(0deg)' },
                    '25%': { transform: 'rotate(-8deg)' },
                    '75%': { transform: 'rotate(8deg)' },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) both',
                'scale-in': 'scale-in 0.35s cubic-bezier(0.2, 0.8, 0.2, 1) both',
                float: 'float 12s ease-in-out infinite',
                'float-slow': 'float 18s ease-in-out infinite reverse',
                'gradient-x': 'gradient-x 6s ease infinite',
                shimmer: 'shimmer 2.2s ease-in-out infinite',
                wiggle: 'wiggle 0.4s ease-in-out',
            },
        },
    },

    plugins: [forms],
};
