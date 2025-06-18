const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/packages/${directory}`
const dist = `public/vendor/core/packages/${directory}`

mix
    .js(`${source}/resources/js/api-settings.js`, `${dist}/js`)
    .sass(`${source}/resources/sass/api-settings.scss`, `${dist}/css`)

if (mix.inProduction()) {
    mix
        .copy(`${dist}/js/api-settings.js`, `${source}/public/js`)
        .copy(`${dist}/css/api-settings.css`, `${source}/public/css`)
}
