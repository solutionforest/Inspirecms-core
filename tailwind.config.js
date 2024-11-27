const preset = require('./vendor/solution-forest/inspirecms-support/tailwind.config.preset')

module.exports = {
    presets: [preset],
    content: [
        './src/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        '../../packages/inspirecms-*/resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}