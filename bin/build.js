import esbuild from 'esbuild'

const isDev = process.argv.includes('--dev')

async function compile(options) {
    const context = await esbuild.context(options)

    if (isDev) {
        await context.watch()
    } else {
        await context.rebuild()
        await context.dispose()
    }
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
    plugins: [{
        name: 'watchPlugin',
        setup: function (build) {
            build.onStart(() => {
                console.log(`Build started at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
            })

            build.onEnd((result) => {
                if (result.errors.length > 0) {
                    console.log(`Build failed at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`, result.errors)
                } else {
                    console.log(`Build finished at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
                }
            })
        }
    }],
}

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/index.js'],
    outfile: './resources/dist/inspirecms.js',
})
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/components/code-editor.js'],
    outfile: './resources/dist/components/code-editor.js',
})
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/components/markdown-editor.js'],
    outfile: './resources/dist/components/markdown-editor.js',
})
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/rich-content-plugins/content-picker-extension.js'],
    outfile: './resources/dist/rich-content-plugins/content-picker-extension.js',
})
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/rich-content-plugins/content-picker-mark.js'],
    outfile: './resources/dist/rich-content-plugins/content-picker-mark.js',
})
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/rich-content-plugins/media-picker-extension.js'],
    outfile: './resources/dist/rich-content-plugins/media-picker-extension.js',
})
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/rich-content-plugins/media-picker-mark.js'],
    outfile: './resources/dist/rich-content-plugins/media-picker-mark.js',
})