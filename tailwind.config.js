/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './archive-template.php',
    './single-template.php',
    './template-*.php',
    './dt-assets/js/**/*.js',
    './dt-assets/parts/**/*.php',
    './dt-*/template-*.php',
    './template-parts/**/*.php'
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        'dt-primary': '#3F729B',
        'dt-secondary': '#8BC34A',
        'dt-success': '#4caf50',
        'dt-warning': '#ffae00',
        'dt-alert': '#cc4b37',
        'dt-gray': {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e6e6e6',
          300: '#cacaca',
          400: '#8a8a8a',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#0a0a0a',
        }
      },
      fontFamily: {
        'sans': ['Helvetica', 'Arial', 'sans-serif'],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      },
      borderRadius: {
        'xl': '0.75rem',
        '2xl': '1rem',
      },
      boxShadow: {
        'card': '0 2px 4px rgba(0,0,0,0.25)',
        'lifted': '0 4px 8px rgba(0,0,0,0.15)',
      },
      screens: {
        'xs': '475px',
      },
      zIndex: {
        '100': '100',
        '1000': '1000',
        '9999': '9999',
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class', // only generate classes
    }),
  ],
  // Prefix all Tailwind classes with 'tw-' to avoid conflicts with Foundation
  prefix: 'tw-',
  // Only apply to specific media queries to avoid conflicts
  corePlugins: {
    preflight: false, // Disable base styles to avoid conflicts with Foundation
  },
  // Safelist important mobile classes
  safelist: [
    'tw-fixed',
    'tw-bottom-0',
    'tw-left-0',
    'tw-right-0',
    'tw-bg-white',
    'tw-shadow-lg',
    'tw-p-4',
    'tw-flex',
    'tw-items-center',
    'tw-justify-between',
    'tw-rounded-full',
    'tw-text-sm',
    'tw-font-medium',
    'tw-transition-all',
    'tw-duration-200',
    'tw-active:bg-gray-50',
    'tw-active:scale-95',
    // Mobile-specific classes
    {
      pattern: /tw-(grid|flex|block|hidden|bg-|text-|p-|m-|rounded-|shadow-)/,
      variants: ['sm', 'md', 'lg', 'xl', 'hover', 'focus', 'active'],
    }
  ]
} 