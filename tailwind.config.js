// tailwind.config.js
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.js',
  ],
  darkMode: false,
  theme: {
    extend: {
      fontFamily: {
        sans: ['Kanit', 'sans-serif']
      },
    },
  },
  plugins: [],
}