// import "./tour-guide";

// Function to disable links within the iframe
function disableIframeLinks(iframe) {
    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
    const links = iframeDocument.querySelectorAll('a');

    links.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
        });
    });
}

document.addEventListener('peek:modal-opened', () => {
    const iframe = document.querySelector('iframe');
    if (iframe) {
        iframe.addEventListener('load', () => {
            disableIframeLinks(iframe);
        });
    }
});

document.addEventListener('alpine:init', () => {
    window.Alpine.store('contentSidebar', {
        isOpen: Alpine.$persist(false).as('contentSidebar_on'),
        reset: function () {
            console.log('resetting sidebar state');
            this.isOpen = false;
        },
    });
});