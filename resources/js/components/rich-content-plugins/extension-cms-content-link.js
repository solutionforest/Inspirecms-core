import { mergeAttributes, Node } from '@tiptap/core'

export default Node.create({
    name: 'cmsContentLink',

    group: 'inline',
    inline: true,
    content: 'text*',
    atom: false,

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
                tag: 'a.trix-attachment-contentpicker',
            },
            {
                tag: 'span.trix-attachment-contentpicker',
            },
        ]
    },

    addCommands() {
        return {
            setCmsContentLink:
                (options = {}) =>
                ({ commands, state, dispatch }) => {
                    const { attributes = {} } = options
                    const { from, to } = state.selection

                    const selectedText = state.doc.textBetween(from, to)
                    const textToUse =
                        selectedText || attributes.title || 'Content Link'

                    const node = state.schema.nodes[this.name].create(
                        attributes,
                        state.schema.text(textToUse),
                    )

                    const tr = state.tr.replaceWith(from, to, node)

                    if (dispatch) {
                        dispatch(tr)
                    }

                    return true
                },
            toggleCmsContentLink:
                (options = {}) =>
                ({ commands, state, dispatch }) => {
                    const { attributes = {} } = options
                    const { from, to, $from } = state.selection

                    // Check if we're inside a cmsContentLink node
                    let cmsContentLinkNode = null
                    let nodePos = null

                    // Walk up the tree to find a cmsContentLink
                    for (let i = $from.depth; i > 0; i--) {
                        const node = $from.node(i)
                        if (node.type.name === this.name) {
                            cmsContentLinkNode = node
                            nodePos = $from.before(i)
                            break
                        }
                    }

                    if (cmsContentLinkNode && nodePos !== null) {
                        // Remove the cmsContentLink, keep the text
                        const textContent = cmsContentLinkNode.textContent
                        const textNode = state.schema.text(textContent)
                        const tr = state.tr.replaceWith(
                            nodePos,
                            nodePos + cmsContentLinkNode.nodeSize,
                            textNode,
                        )

                        if (dispatch) {
                            dispatch(tr)
                        }

                        return true
                    } else {
                        // Wrap selected text in cmsContentLink
                        const selectedText = state.doc.textBetween(from, to)
                        const textToUse =
                            selectedText || attributes.title || 'Content Link'

                        const node = state.schema.nodes[this.name].create(
                            attributes,
                            state.schema.text(textToUse),
                        )

                        const tr = state.tr.replaceWith(from, to, node)

                        if (dispatch) {
                            dispatch(tr)
                        }

                        return true
                    }
                },
            insertCmsContentLink:
                (options = {}) =>
                ({ commands, state, dispatch }) => {
                    const { attributes = {}, content = null } = options
                    const textContent =
                        content || attributes.title || 'Content Link'

                    const node = state.schema.nodes[this.name].create(
                        attributes,
                        state.schema.text(textContent),
                    )

                    const { from } = state.selection
                    const tr = state.tr.insert(from, node)

                    if (dispatch) {
                        dispatch(tr)
                    }

                    return true
                },
        }
    },

    renderHTML({ HTMLAttributes, node }) {
        const { url, title, target } = node.attrs

        return [
            'a',
            mergeAttributes(HTMLAttributes, {
                class: 'trix-attachment-contentpicker',
                'data-content-id': node.attrs.id,
                'data-content-slug': node.attrs.slug,
                'data-url': url,
                'data-title': title,
                'data-target': target,
                href: url || '#',
                target: target || '_self',
            }),
            0, // This allows the content (text) to be rendered inside the element
        ]
    },
})
