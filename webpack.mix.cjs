// webpack.mix.js
// Documentaion https://laravel-mix.com/docs/6.0/installation
let mix = require('laravel-mix');

mix.webpackConfig({
    // stats: {
    //         children: true
    // }
    output: {
        publicPath: '/wp-content/plugins/ronik-base/admin/interface/dist/'
    },
    module: {
        rules: [
            {
                test: /\.svg$/,
                type: 'asset/resource',
                generator: {
                    filename: 'images/[name][ext]'
                }
            }
        ]
    }
});
mix
    // Register watch files.
        // Public Facing
    .js('public/assets/src/app.js', 'public/assets/dist/') // creates 'dist/app.js'
    .sass('public/assets/src/sass/main.scss', 'public/assets/dist/') // creates 'dist/main.css'
        // Admin Facing
    .js('admin/interface/index.js', 'admin/interface/dist/') // creates 'dist/app.js'
    .sass('admin/interface/src/sass/main.scss', 'admin/interface/dist/') // creates 'dist/main.css'



    // .js('public/assets/src/app.js', 'public/assets/dist/') // creates 'dist/app.js'
    // .sass('public/assets/src/sass/main.scss', 'public/assets/dist/') // creates 'dist/main.css'
    // Minfies all included files and append .min | Minification of files will only work when npm run production runs.
    // .minify(['public/assets/dist/main.css', 'public/assets/dist/app.js']);
    // Minfies all included files and append .min | Minification of files will only work when npm run production runs.
    // .minify(['admin/interface/dist/main.css', 'admin/interface/dist/app.js']);

