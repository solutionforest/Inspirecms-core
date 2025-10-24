import { mergeAttributes, Node, Extension } from '@tiptap/core'

const convertResponsiveAttributes = (element) => {
    const responsiveData = {}
    // Ensure the element is an object, key-value pairs, key is breakpoint, value is URL
    // If it's an array, convert to object
    if (Array.isArray(element)) {
        element.forEach((item) => {
            const { breakpoint, url } = item
            responsiveData[`data-responsive_${breakpoint}`] = url
        })
    }
    // If it's already an object
    else if (typeof element === 'object' && element !== null) {
        Object.keys(element).forEach((breakpoint) => {
            responsiveData[`data-responsive__${breakpoint}`] =
                element[breakpoint]
        })
    }
    return responsiveData
}

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
                    element.getAttribute('data-cmsmediaasset-id'),
                renderHTML: (attributes) => {
                    if (!attributes.id) return {}
                    return { 'data-cmsmediaasset-id': attributes.id }
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
            responsive: {
                default: [],
                renderHTML: (attributes) => {
                    return convertResponsiveAttributes(
                        attributes.responsive || [],
                    )
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
        const {
            title,
            url,
            mimeType,
            filename,
            thumbnailUrl = null,
        } = node.attrs

        const getType = () => {
            if (mimeType) {
                if (mimeType.startsWith('image/')) {
                    return 'img'
                } else if (mimeType.startsWith('video/')) {
                    return 'video'
                } else if (mimeType.startsWith('audio/')) {
                    return 'audio'
                }
            }
            return 'file'
        }

        // Build the inner content as proper HTML elements
        let innerContent
        if (getType(mimeType) === 'img') {
            // Create img element
            innerContent = [
                'img',
                {
                    src: thumbnailUrl ?? url,
                    alt: title || 'Media Asset',
                },
            ]
        } else {
            // Just text content
            innerContent = title || 'Media Asset'
        }

        let mediaTypeAttribute = {}
        if (getType(mimeType) !== 'img') {
            mediaTypeAttribute = { style: 'text-decoration: underline;' }
        } else {
            if (!filename.endsWith('.svg')) {
                mediaTypeAttribute = {
                    ...convertResponsiveAttributes(node.attrs.responsive || []),
                }
            }
        }

        return [
            'span',
            mergeAttributes(
                HTMLAttributes,
                {
                    class: 'trix-attachment-mediapicker',
                    'data-mediaasset-id': node.attrs.id,
                    'data-url': url,
                    'data-mime-type': mimeType,
                    'data-filename': filename,
                },
                mediaTypeAttribute,
                thumbnailUrl && {
                    'data-thumbnail-url': thumbnailUrl,
                },
            ),
            innerContent,
        ]
    },
})
