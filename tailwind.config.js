/** @type {import('tailwindcss').Config} */
// const width = {};
// for (let i = 1; i <= 5; i++) {
//   width[`f${i}`] = `${i}%`;
// }
// class = "wf-100%" berarti width: 100%, cara nulisnya bukan bukan w-[100%] (seperti di website tailwind)
// width['foo1'] = '100%'
// width['foo'] = '100%'
// width['bar'] = '100%'
// ga bisa kita membuat width['foo1'] karena tercampur angka dan huruf ('foo1');

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/**/*.xsl",
  ],
  theme: {
    extend: {
      // width: {
      //   'foo': '100%'
      // }
      fontSize: {
        '2xs' : ['0.5rem', {
          lineHeight: '0.75rem'
        }], 
      }
    },
    fontFamily: {
      'tahoma': ['tahoma', 'system-ui']
    }
  },
  plugins: [],
}

// /** @type {import('tailwindcss').Config} */
// module.exports = {
//   content: [
//     "./resources/**/*.blade.php",
//     "./resources/**/*.js",
//     "./resources/**/*.vue",
//     "./resources/**/*.xsl",
//   ],
//   theme: {
//     extend: {},
//     fontFamily: {
//         'tahoma': ['tahoma', 'system-ui']
//     }
//   },
//   plugins: [],
// }