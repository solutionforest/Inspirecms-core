const theme = require('tailwindcss/defaultTheme')
const preset = require('./vendor/filament/filament/tailwind.config.preset')

module.exports = {
    theme: {
        extend: {
            content: {
                'close': 'url("data:image/svg+xml,%3Csvg viewBox=\'0 0 24 24\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg id=\'SVGRepo_bgCarrier\' stroke-width=\'0\'%3E%3C/g%3E%3Cg id=\'SVGRepo_tracerCarrier\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3C/g%3E%3Cg id=\'SVGRepo_iconCarrier\'%3E %3Crect width=\'24\' height=\'24\' fill=\'white\'%3E%3C/rect%3E %3Cpath d=\'M7 17L16.8995 7.10051\' stroke=\'currentColor\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3C/path%3E %3Cpath d=\'M7 7.00001L16.8995 16.8995\' stroke=\'currentColor\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3C/path%3E %3C/g%3E%3C/svg%3E")'
            },
        }
    },
    presets: [preset],
    content: [
        './src/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        '../../packages/inspirecms-*/resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}