import { Mark } from '@tiptap/core';

export const ContentPickerLink = Mark.create({

    name: 'contentPickerLink',

    addAttributes() {
        return {
            href: { default: null },
            target: { default: '_blank' },
            title: { default: null },
            'data-content-id': { default: null },
            'data-content-slug': { default: null },
            class: { default: 'trix-attachment-contentpicker' },
            rel: { default: 'noopener noreferrer' },
        };
    },
    
    parseHTML() {
        return [
            { tag: 'a.trix-attachment-contentpicker' },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['a', HTMLAttributes, 0];
    },
});

export default ContentPickerLink;