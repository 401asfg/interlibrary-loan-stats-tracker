const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .vue()
   .css('resources/css/app.css', 'public/css');
