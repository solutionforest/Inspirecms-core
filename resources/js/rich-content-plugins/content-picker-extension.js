import { Extension } from '@tiptap/core';

export default Extension.create({
    
    name: 'contentPickerLinkExtension',
    
    addCommands() {
        return {
            insertContentPickerLinks:
            (contentItems) =>
            ({ commands }) => {

                if (!contentItems || !Array.isArray(contentItems) || contentItems.length === 0) {
                    return;
                }
               
                contentItems.forEach((item, index) => {
                    const { url, title, target, key, slug } = item;
                    commands.insertContent({
                        type: 'text',
                        text: title || url,
                        marks: [
                            {
                                type: 'contentPickerLink',
                                attrs: {
                                    href: url,
                                    target: target || '_blank',
                                    title: title || null,
                                    'data-content-id': key || null,
                                    'data-content-slug': slug || null,
                                },
                            }
                        ]
                    })
                })

                return true;
            }
        }
    },
});