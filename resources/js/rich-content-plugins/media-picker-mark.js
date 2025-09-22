import { Mark } from '@tiptap/core';

const MediaPickerLink = Mark.create({

    name: 'mediaPickerLink',
    
    addOptions() {
        return {
            // ...this.parent?.(),
            HTMLAttributes: {
                class: 'media-picker-link',
            },
            // openOnClick: true,
            // linkOnPaste: true,
            // autolink: true,
            conversions: (items) => {
                console.log('MediaPickerLink conversions items:', items);
                return [];
            }
        };
    },

    addAttributes() {
        return {
            // ...this.parent?.(),
            href: {
                default: null,
            },
            target: {
                default: '_blank',
            },
            alt: {
                default: null,
            },
            class: {
                default: 'media-picker-link',
            },
            'data-media-type': {
                default: null,
            },
            'data-media-id': {
                default: null,
            },
        };
    },

    // addAttributes() {
    //     return {
    //         href: { default: null },
    //         target: { default: '_blank' },
    //         title: { default: null },
    //         'data-media-id': { default: null },
    //         class: { default: 'trix-attachment-mediapicker' },
    //         rel: { default: 'noopener noreferrer' },
    //     };
    // },
    
    parseHTML() {
        console.log('MediaPickerLink parseHTML called');
        console.log(this.options);
        console.log(this.parent);
        return [
            { tag: 'a.trix-attachment-mediapicker' },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        console.log('MediaPickerLink parseHTML called');
        console.log(this.options);
        console.log(this.parent);
        console.log(HTMLAttributes);
        return ['a', HTMLAttributes, 0];
    },
});

export default MediaPickerLink;