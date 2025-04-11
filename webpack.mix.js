const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/math-field.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css')
   .copy('node_modules/katex/dist/katex.min.css', 'public/css')
   .copy('node_modules/katex/dist/fonts', 'public/fonts');
   

// mix.js('resources/js/app.js', 'public/js')
//    .postCss('resources/css/app.css', 'public/css');