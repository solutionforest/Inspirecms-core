import { mergeAttributes, Node, Extension } from '@tiptap/core'

export default Node.create({
    name: 'cmsMediaAsset',

    group: 'inline', // Crucially, set the group to 'inline'
    inline: true, // Mark it as an inline node
    atom: true, // If it's a self-contained unit without editable content inside

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: (element) =>
                    element.getAttribute('data-mediaasset-id'),
                renderHTML: (attributes) => {
                    if (!attributes.id) return {}
                    return { 'data-mediaasset-id': attributes.id }
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
            thumbnailUrl: {
                default: null,
                parseHTML: (element) =>
                    element.getAttribute('data-thumbnail-url'),
                renderHTML: (attributes) => {
                    if (!attributes.thumbnailUrl) return {}
                    return { 'data-thumbnail-url': attributes.thumbnailUrl }
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
            mimeType: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-mime-type'),
                renderHTML: (attributes) => {
                    if (!attributes.mimeType) return {}
                    return { 'data-mime-type': attributes.mimeType }
                },
            },
            filename: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-filename'),
                renderHTML: (attributes) => {
                    if (!attributes.filename) return {}
                    return { 'data-filename': attributes.filename }
                },
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span.trix-attachment-mediapicker',
            },
        ]
    },

    renderHTML({ HTMLAttributes, node }) {
        const { thumbnailUrl, title, url, mimeType, filename } = node.attrs

        // Build the inner content as proper HTML elements
        let innerContent
        if (
            thumbnailUrl &&
            mimeType.startsWith('image/') &&
            !filename.endsWith('.svg')
        ) {
            // Create img element
            innerContent = [
                'img',
                {
                    src: thumbnailUrl,
                    alt: title || 'Media Asset',
                },
            ]
        } else {
            // Just text content
            innerContent = title || 'Media Asset'
        }

        return [
            'span',
            mergeAttributes(HTMLAttributes, {
                class: 'trix-attachment-mediapicker',
                'data-mediaasset-id': node.attrs.id,
                'data-url': url,
                'data-mime-type': mimeType,
                'data-filename': filename,
            }),
            innerContent,
        ]
    },
})
