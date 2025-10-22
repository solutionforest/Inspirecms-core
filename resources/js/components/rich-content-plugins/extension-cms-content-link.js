import { mergeAttributes, Node } from '@tiptap/core'

export default Node.create({
    name: 'cmsContentLink',

    group: 'inline',
    inline: true,
    atom: true,

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-content-id'),
                renderHTML: (attributes) => {
                    if (!attributes.id) return {}
                    return { 'data-content-id': attributes.id }
                },
            },
            slug: {
                default: null,
                parseHTML: (element) =>
                    element.getAttribute('data-content-slug'),
                renderHTML: (attributes) => {
                    if (!attributes.slug) return {}
                    return {
                        'data-content-slug': attributes.slug,
                    }
                },
            },
            url: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-url'),
                renderHTML: (attributes) => {
                    if (!attributes.url) return {}
                    return { 'data-url': attributes.url }
                },
            },
            title: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-title'),
                renderHTML: (attributes) => {
                    if (!attributes.title) return {}
                    return { 'data-title': attributes.title }
                },
            },
            target: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-target'),
                renderHTML: (attributes) => {
                    if (!attributes.target) return {}
                    return { 'data-target': attributes.target }
                },
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span.trix-attachment-contentpicker',
            },
        ]
    },

    renderHTML({ HTMLAttributes, node }) {
        const { url, title, target } = node.attrs

        // Create the link content
        const innerContent = ['a', {}, title || 'Content Link']

        return [
            'span',
            mergeAttributes(HTMLAttributes, {
                class: 'trix-attachment-contentpicker',
                'data-content-id': node.attrs.id,
                'data-content-slug': node.attrs.slug,
                'data-url': url,
                'data-title': title,
                'data-target': target,
            }),
            innerContent,
        ]
    },
})
