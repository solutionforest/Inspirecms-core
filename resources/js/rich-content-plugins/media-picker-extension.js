import { Extension } from '@tiptap/core';

const appendMediaLinkList = (editor, mediaLinks) => {
    if (!editor || !Array.isArray(mediaLinks) || mediaLinks.length === 0) {
        return;
    }

    // Get current position
    const { selection } = editor.state;
    const { $from } = selection;
    
    mediaLinks.forEach((mediaData, index) => {
        const { url, text, title, target, type, id, conversions, srcset } = mediaData;

        console.log('Inserting media link:', mediaData);
        
        // Insert a line break if not the first link and not at start of document
        if (index > 0 || $from.pos > 0) {
            editor.commands.insertContent('<br>');
        }
        
        // Insert the media link with appropriate styling
        editor.commands.insertContent({
            type: 'text',
            text: text || url,
            marks: [
                {
                    type: 'mediaPickerLink',
                    conversions: conversions || null,
                    srcset: srcset || null,
                    attrs: {
                        href: url,
                        target: target || '_blank',
                        alt: title || null,
                        class: `media-picker-link ${type ? `media-${type}` : 'media-generic'}`,
                        'data-media-type': type || 'generic',
                        'data-media-id': id || null,
                    }
                }
            ]
        });
    });
};

const MediaPickerLinkExtension = Extension.create({

    name: 'mediaPickerLinkExtension',

    addCommands() {
        return {
            insertMediaPickerLinks: (mediaItems) => ({ editor }) => {
                if (!Array.isArray(mediaItems) || mediaItems.length === 0) {
                    return false;
                }

                console.log('Inserting media items:', mediaItems);

                const mediaLinks = mediaItems.map(item => {
                    // Detect media type from URL or mime type
                    const detectMediaType = (url, mimeType) => {
                        if (mimeType) {
                            if (mimeType.startsWith('image/')) return 'image';
                            if (mimeType.startsWith('video/')) return 'video';
                            if (mimeType.startsWith('audio/')) return 'audio';
                            if (mimeType.includes('pdf')) return 'pdf';
                        }
                        
                        if (url) {
                            const ext = url.split('.').pop()?.toLowerCase();
                            if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) return 'image';
                            if (['mp4', 'avi', 'mov', 'webm', 'mkv'].includes(ext)) return 'video';
                            if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) return 'audio';
                            if (ext === 'pdf') return 'pdf';
                        }
                        
                        return 'generic';
                    };

                    return {
                        url: item.url || item.src || item.href,
                        text: item.title || item.name || item.alt || 'Media Link',
                        title: item.description || item.title,
                        target: '_blank',
                        type: detectMediaType(item.url || item.src, item.mimeType || item.type),
                        id: item.id || item.mediaId || null,
                        conversions: item.conversions || item.sizes || null,
                        srcset: item.srcset || null,
                    };
                });

                appendMediaLinkList(editor, mediaLinks);
                return true;
            }
        }
    }
});

export default MediaPickerLinkExtension;

// // document.addEventListener('alpine:init', () => {
// //     window.Alpine.magic('insertRichMediaPicker', () => insertMediaAssets)
// // })

// export default Mark.create({
//     name: 'mediaPickerLink',
    
//     addOptions() {
//         return {
//             ...this.parent?.(),
//             HTMLAttributes: {
//                 class: 'media-picker-link',
//             },
//             openOnClick: true,
//             linkOnPaste: true,
//             autolink: true,
//         };
//     },

//     addAttributes() {
//         return {
//             ...this.parent?.(),
//             href: {
//                 default: null,
//             },
//             target: {
//                 default: '_blank',
//             },
//             title: {
//                 default: null,
//             },
//             class: {
//                 default: 'media-picker-link',
//             },
//             'data-media-type': {
//                 default: null,
//             },
//         };
//     },

//     renderHTML({ HTMLAttributes }) {
//         return [
//             'a',
//             mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
//             0,
//         ];
//     },

//     addCommands() {
//         return {
//             ...this.parent?.(),
            
//             // Command to append a single media link
//             appendMediaLink: (options) => ({ commands }) => {
//                 const { url, text, title, target, type } = options;
//                 return commands.insertContent({
//                     type: 'text',
//                     text: text || url,
//                     marks: [
//                         {
//                             type: 'link',
//                             attrs: {
//                                 href: url,
//                                 target: target || '_blank',
//                                 title: title || null,
//                                 class: `media-picker-link ${type ? `media-${type}` : 'media-generic'}`,
//                                 'data-media-type': type || 'generic'
//                             }
//                         }
//                     ]
//                 });
//             },

//             // Command to append multiple media links
//             appendMediaLinkList: (mediaLinks) => ({ editor }) => {
//                 appendMediaLinkList(editor, mediaLinks);
//                 return true;
//             },

//             // Command to insert media picker links with automatic type detection
//             insertMediaPickerLinks: (mediaItems) => ({ editor }) => {
//                 if (!Array.isArray(mediaItems) || mediaItems.length === 0) {
//                     return false;
//                 }

//                 const mediaLinks = mediaItems.map(item => {
//                     // Detect media type from URL or mime type
//                     const detectMediaType = (url, mimeType) => {
//                         if (mimeType) {
//                             if (mimeType.startsWith('image/')) return 'image';
//                             if (mimeType.startsWith('video/')) return 'video';
//                             if (mimeType.startsWith('audio/')) return 'audio';
//                             if (mimeType.includes('pdf')) return 'pdf';
//                         }
                        
//                         if (url) {
//                             const ext = url.split('.').pop()?.toLowerCase();
//                             if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) return 'image';
//                             if (['mp4', 'avi', 'mov', 'webm', 'mkv'].includes(ext)) return 'video';
//                             if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) return 'audio';
//                             if (ext === 'pdf') return 'pdf';
//                         }
                        
//                         return 'generic';
//                     };

//                     return {
//                         url: item.url || item.src || item.href,
//                         text: item.title || item.name || item.alt || 'Media Link',
//                         title: item.description || item.title,
//                         target: '_blank',
//                         type: detectMediaType(item.url || item.src, item.mimeType || item.type)
//                     };
//                 });

//                 appendMediaLinkList(editor, mediaLinks);
//                 return true;
//             }
//         };
//     },

//     // // Add keyboard shortcuts for media
//     // addKeyboardShortcuts() {
//     //     return {
//     //         ...this.parent?.(),
//     //         'Mod-Shift-m': () => {
//     //             // Custom shortcut for media link insertion
//     //             const mediaLinks = [
//     //                 { url: 'image.jpg', text: 'Sample Image', type: 'image' },
//     //                 { url: 'video.mp4', text: 'Sample Video', type: 'video' }
//     //             ];
//     //             return this.editor.commands.appendMediaLinkList(mediaLinks);
//     //         }
//     //     };
//     // }
// });