/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme');
const plugin = require('tailwindcss/plugin');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Sora', 'Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Premium enterprise identity: green primary (cybersecurity/
                // IT support), blue secondary (technology/cloud/software).
                // `brand` and `primary` share the green scale; `cyber` is the
                // blue secondary. Semantic palettes stay reserved for status.
                brand: {
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#16A34A',
                    600: '#15803D',
                    700: '#166534',
                    800: '#14532D',
                    900: '#052E16',
                },
                primary: {
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#16A34A',
                    600: '#15803D',
                    700: '#166534',
                    800: '#14532D',
                    900: '#052E16',
                },
                surface: {
                    0: '#FFFFFF',
                    50: '#F8FAFC',
                    100: '#F1F5F9',
                    200: '#E2E8F0',
                    300: '#CBD5E1',
                    400: '#94A3B8',
                    500: '#64748B',
                    600: '#475569',
                    700: '#334155',
                    800: '#1E293B',
                    900: '#0F172A',
                },
                navy: {
                    50: '#F0F3F9',
                    100: '#DCE3F0',
                    200: '#B9C7E1',
                    300: '#8FA5CC',
                    400: '#6B84B5',
                    500: '#4F6A9E',
                    600: '#3D5483',
                    700: '#2F4168',
                    800: '#0F172A',
                    900: '#0A0F1E',
                    950: '#060A14',
                },
                cyber: {
                    100: '#DBEAFE',
                    200: '#BFDBFF',
                    300: '#93C5FD',
                    400: '#3B82F6',
                    500: '#2563EB',
                    600: '#1D4ED8',
                    700: '#1E3A8A',
                    800: '#1E40AF',
                    900: '#172554',
                },
                // PERFECTITSECURITY terminal palette: near-black charcoal,
                // electric-green accent used sparingly, electric-blue secondary.
                term: {
                    0: '#050807',
                    50: '#0a0f0d',
                    100: '#0d1310',
                    200: '#111815',
                    300: '#16201c',
                    400: '#1d2a24',
                    500: '#2a3a32',
                    600: '#3d5247',
                    700: '#5d7a6a',
                    800: '#8ba595',
                    900: '#c2d4c8',
                    950: '#e6f0ea',
                },
                accent: {
                    DEFAULT: '#00E67A',
                    dim: '#00B35F',
                    soft: '#5CEFA8',
                    blue: '#4DA3FF',
                    amber: '#FFB454',
                    red: '#FF5C5C',
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-in-left': 'slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                'counter': 'counter 2s cubic-bezier(0.16, 1, 0.3, 1)',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'float': 'float 3s ease-in-out infinite',
            },
            boxShadow: {
                'premium-sm': '0 1px 2px rgba(2,6,23,0.06), 0 4px 14px -4px rgba(2,6,23,0.10)',
                'premium': '0 12px 40px -12px rgba(2,6,23,0.18), 0 2px 8px -2px rgba(2,6,23,0.08)',
                'premium-lg': '0 24px 70px -20px rgba(2,6,23,0.28), 0 4px 16px -4px rgba(2,6,23,0.10)',
                'glow-brand': '0 10px 30px -8px rgba(22,163,74,0.45), 0 4px 14px -4px rgba(37,99,235,0.35)',
            },
            borderRadius: {
                'xl2': '1.1rem',
                'xl3': '1.4rem',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInLeft: {
                    '0%': { opacity: '0', transform: 'translateX(-30px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
            },
            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
                '26': '6.5rem',
                '30': '7.5rem',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        plugin(function({ addUtilities }) {
            addUtilities({
                '.glass': {
                    'background': 'rgba(255, 255, 255, 0.1)',
                    'backdrop-filter': 'blur(10px)',
                    'border': '1px solid rgba(255, 255, 255, 0.2)',
                },
                '.preserve-3d': {
                    'transform-style': 'preserve-3d',
                },
                '.backface-hidden': {
                    'backface-visibility': 'hidden',
                },
            });
        }),
    ],
}
