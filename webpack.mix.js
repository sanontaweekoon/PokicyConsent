const mix = require('laravel-mix')

mix.js('resources/js/main.js', 'public/js')
   .vue()
   .postCss('resources/css/app.css', 'public/css')
   .version()