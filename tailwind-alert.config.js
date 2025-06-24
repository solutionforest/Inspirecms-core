const preset = require('./vendor/solution-forest/inspirecms-support/tailwind.config.preset')

module.exports = {
    presets: [preset],
    content: [
        './resources/views/components/alert/*.blade.php'
    ],
    theme: {
        extend: {
            keyframes: {
                scroll: {
                    '0%': { transform: 'translateX(100%)' },
                    '100%': { transform: 'translateX(-100%)' },
                },
            },
        }
    }
}