/** @type {import('tailwindcss').Config} */
const defaultTheme = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

module.exports = {
  important: "#jexy-dummy-admin",
  content: [
    "./plugin/admin/src/**/*.{html,js}",
    "./plugin/admin/views/*.php",
  ],
  theme: {
    colors: {
      transparent: "transparent",
      current: "currentColor",
      black: colors.black,
      white: colors.white,
      gray: colors.neutral,
      red: colors.red,
      yellow: colors.amber,
      green: colors.green,
      blue: colors.blue,
      // indigo: colors.indigo,
      indigo: colors.sky,
      purple: colors.purple,
      pink: colors.pink,
    },
  },
  extend: {
    fontFamily: {
      sans: ["Inter var", ...defaultTheme.fontFamily.sans],
    },
  },
  plugins: [require("@tailwindcss/forms"), require("@tailwindcss/typography")],
};
