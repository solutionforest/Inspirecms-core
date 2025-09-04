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
    getExtraToolbarButtonUsing,
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

        getToolbarButton: (name) => {

            const extraBtn =  getExtraToolbarButtonUsing ? getExtraToolbarButtonUsing(name) : null;

            return extraBtn ?? base.getToolbarButton(name);
        }
    };

    return data;
}