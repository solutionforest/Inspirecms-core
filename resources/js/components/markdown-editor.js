import markdownEditorFormComponent from '../../../vendor/filament/forms/resources/js/components/markdown-editor.js';

export default function markdownEditorEnhancedFormComponent({
    canAttachFiles,
    isLiveDebounced,
    isLiveOnBlur,
    liveDebounce,
    maxHeight,
    minHeight,
    placeholder,
    setUpUsing,
    state,
    translations,
    toolbarButtons,
    uploadFileAttachmentUsing,
    getExtraToolbarButtonsUsing,
}) {
    let base = markdownEditorFormComponent({
        canAttachFiles,
        isLiveDebounced,
        isLiveOnBlur,
        liveDebounce,
        maxHeight,
        minHeight,
        placeholder,
        setUpUsing,
        state,
        translations,
        toolbarButtons,
        uploadFileAttachmentUsing,
    });

    let data = {
        ...base,
        getToolbar: () => {
            
            let toolbar = base.getToolbar();

            const extraButtons = getExtraToolbarButtonsUsing ? getExtraToolbarButtonsUsing(toolbarButtons) : [];

            // Add before "undo" button
            toolbar.splice(toolbar.findIndex(button => button.name === 'undo'), 0, ...extraButtons);

            return toolbar;
        }
    };

    return data;
}