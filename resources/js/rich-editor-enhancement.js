const insertMediaAssets = (trixElem, state) => {
    const editor = trixElem.editor;
    if (!editor || !state || !Array.isArray(state) || state.length === 0) {
        return;
    }
    state.forEach(item => {
        const attachment = new Trix.Attachment(item);
        editor.insertAttachment(attachment);
    });
}

const insertContent = (trixElem, state) => {
    const editor = trixElem.editor;
    if (!editor || !state || !Array.isArray(state) || state.length === 0) {
        return;
    }
    state.forEach(item => {
        editor.insertHTML(item);
    });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.magic('insertRichMediaPicker', () => insertMediaAssets)
    window.Alpine.magic('insertRichContentPicker', () => insertContent)
})