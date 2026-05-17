import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                paper: {
                    bg: '#F8F9FA', 
                    card: '#FFFFFF',
                    ink: '#1E3A8A', 
                    marker: '#BAE6FD',
                    border: '#E2E8F0',
                    muted: '#64748B',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Georgia', ...defaultTheme.fontFamily.serif],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                paper: '0 18px 40px rgba(30, 58, 138, 0.10), 0 2px 8px rgba(30, 58, 138, 0.06)',
                'paper-hover': '0 24px 54px rgba(30, 58, 138, 0.16), 0 3px 10px rgba(30, 58, 138, 0.08)',
                clip: '0 7px 14px rgba(30, 58, 138, 0.18)',
            },
            keyframes: {
                'paper-in': {
                    '0%': { opacity: '0', transform: 'translateY(12px) rotate(-0.4deg)' },
                    '100%': { opacity: '1', transform: 'translateY(0) rotate(0deg)' },
                },
                'marker-swipe': {
                    '0%': { transform: 'scaleX(0)' },
                    '100%': { transform: 'scaleX(1)' },
                },
            },
            animation: {
                'paper-in': 'paper-in 0.45s ease-out both',
                'marker-swipe': 'marker-swipe 0.55s ease-out both',
            },
        },
    },
    plugins: [],
};
