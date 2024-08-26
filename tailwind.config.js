/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.jsx",
        "./resources/**/*.vue",
        "./index.html",
        "./src/**/*.{ts,jsx,tsx}",
    ],
  theme: {
    extend: {
      colors:{
          primary: "#0053B3",
          secondary : "#FF9500"
      },
      // container:{
      //     center : true,
      //     padding:{
      //       DEFAULT : "1rem",
      //       sm: "2rem"
      //     }
      // }
      maxWidth: {
        container: "1440px",
      },
      screens: {
        xs: "320px",
        sm: "375px",
        sml: "500px",
        md: "667px",
        mdl: "768px",
        lg: "960px",
        lgl: "1024px",
        xl: "1280px",
      },
    },
  },
  plugins: [
    require('tailwind-scrollbar'),
  ],
}

