import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

const accent = {
  50: "#FAF6F0",
  100: "#F1E8DA",
  200: "#E6D6BF",
  300: "#D8C0A0",
  400: "#C3A27D",
  500: "#A98660",
  600: "#8A6A4E",
  700: "#6B4F3A",
  800: "#4B3828",
  900: "#3A2B1F",
  950: "#2A2019",
};

const warm = {
  50: "#FBF8F3",
  100: "#F4EEE5",
  200: "#E9DFD0",
  300: "#D6C8B4",
  400: "#A89A88",
  500: "#85796A",
  600: "#6B6054",
  700: "#51473D",
  800: "#3A322A",
  900: "#2A2019",
  950: "#1B1510",
};

export default {
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./public/js/**/*.js",
  ],

  theme: {
    extend: {
      colors: {
        white: "#FFFDF9",
        cream: "#F8F3EA",
        sand: "#E9DDCB",
        beige: "#D8C5AA",
        taupe: "#A88F74",
        mocha: "#8A6A4E",
        espresso: "#4B3828",
        ink: "#2A2019",
        indigo: accent,
        blue: accent,
        violet: accent,
        purple: accent,
        slate: warm,
        gray: warm,
        zinc: warm,
        neutral: warm,
      },
      borderRadius: {
        none: "0px",
        sm: "5px",
        DEFAULT: "5px",
        md: "6px",
        lg: "8px",
        xl: "12px",
        "2xl": "16px",
        "3xl": "20px",
        full: "20px",
      },
      fontFamily: {
        sans: ["Lora", "Georgia", ...defaultTheme.fontFamily.serif],
        serif: ["Cormorant Garamond", "Georgia", ...defaultTheme.fontFamily.serif],
      },
    },
  },

  plugins: [forms],
};
