// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.html",
    "./js/*.js",
    // Remove the overly broad pattern that caused the warning
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}