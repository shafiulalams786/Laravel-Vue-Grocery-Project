/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'Hind Siliguri', 'system-ui', 'sans-serif'],
        bn:   ['Hind Siliguri', 'sans-serif'],
      },
      colors: {
        brand: {
          50:  '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
          300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
          600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
        },
        surface: { DEFAULT: '#ffffff', soft: '#f8fafc', muted: '#f1f5f9' },
        border:  { DEFAULT: '#e2e8f0', strong: '#cbd5e1' },
        ink:     { DEFAULT: '#0f172a', secondary: '#475569', muted: '#94a3b8' },
      },
      borderRadius: {
        xl: '14px', '2xl': '20px', '3xl': '28px',
      },
      boxShadow: {
        card:  '0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04)',
        lift:  '0 8px 32px rgba(0,0,0,.10)',
        glow:  '0 0 0 3px rgba(16,185,129,.25)',
        'glow-sm': '0 0 0 2px rgba(16,185,129,.20)',
      },
      animation: {
        'fade-up':    'fadeUp .3s ease both',
        'fade-in':    'fadeIn .2s ease both',
        'scale-in':   'scaleIn .2s cubic-bezier(.34,1.56,.64,1) both',
        'slide-up':   'slideUp .3s cubic-bezier(.4,0,.2,1) both',
        'shimmer':    'shimmer 2.5s linear infinite',
        'spin-slow':  'spin 2s linear infinite',
      },
      keyframes: {
        fadeUp:   { from: { opacity: 0, transform: 'translateY(10px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
        fadeIn:   { from: { opacity: 0 }, to: { opacity: 1 } },
        scaleIn:  { from: { opacity: 0, transform: 'scale(.94)' }, to: { opacity: 1, transform: 'scale(1)' } },
        slideUp:  { from: { opacity: 0, transform: 'translateY(16px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
        shimmer:  { '0%': { backgroundPosition: '-200% center' }, '100%': { backgroundPosition: '200% center' } },
      },
    },
  },
  plugins: [],
}
