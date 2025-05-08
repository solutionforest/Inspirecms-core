const preset = require('./vendor/solution-forest/inspirecms-support/tailwind.config.preset')

module.exports = {
    presets: [preset],
    content: [
        './resources/views/filament/forms/components/code-editor.blade.php'
    ],
}