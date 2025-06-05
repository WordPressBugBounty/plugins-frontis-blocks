document.addEventListener("DOMContentLoaded", function () {
    setTimeout(() => {
        const iframe = document.querySelector('iframe.edit-site-visual-editor__editor-canvas');

        if (!iframe) {
            return;
        }

        // Check if iframe is already loaded
        if (iframe.contentDocument && iframe.contentDocument.readyState === "complete") {
            injectStyleIntoIframe(iframe);
        } else {
            iframe.onload = function () {
                injectStyleIntoIframe(iframe);
            };
        }
    }, 10000);
});

function injectStyleIntoIframe(iframe) {
    const style = document.createElement('style');
    style.id = "fb-responsive-controls";
    style.textContent = `
        @media only screen and (min-width: 1025px) {
            .fb-desktop-responsive > div {
                opacity: .4;
            }
            .fb-desktop-responsive::after {
                position: absolute;
                content: '';
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                background: repeating-linear-gradient(125deg, rgba(0, 0, 0, .05), rgba(0, 0, 0, .05) 1px, transparent 2px, transparent 9px) !important;
                z-index: 1;
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 1024px) {
            .fb-tablet-responsive > div {
                opacity: .4;
            }
            .fb-tablet-responsive::after {
                position: absolute;
                content: '';
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                background: repeating-linear-gradient(125deg, rgba(0, 0, 0, .05), rgba(0, 0, 0, .05) 1px, transparent 2px, transparent 9px) !important;
                z-index: 1;
            }
        }

        @media only screen and (max-width: 767px) {
            .fb-mobile-responsive > div {
                opacity: .4;
            }
            .fb-mobile-responsive::after {
                position: absolute;
                content: '';
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                background: repeating-linear-gradient(125deg, rgba(0, 0, 0, .05), rgba(0, 0, 0, .05) 1px, transparent 2px, transparent 9px) !important;
                z-index: 1;
            }
        }
    `;

    if (iframe.contentDocument && iframe.contentDocument.head) {
        iframe.contentDocument.head.appendChild(style);
    } else {
        console.error("Iframe head not accessible!");
    }
}


document.addEventListener("DOMContentLoaded", function () {
    setTimeout(() => {
        // Select the device preview toggle button
        const deviceBtn = document.querySelector('.editor-preview-dropdown__toggle');

        if (!deviceBtn) {
            console.error("Device button not found!");
            return;
        }

        deviceBtn.addEventListener("click", function() {
            setTimeout(() => {
                // Select all buttons inside the popover container
                const buttons = document.querySelectorAll('.components-popover__fallback-container .components-menu-item__button');

                buttons.forEach(button => {
                    button.addEventListener("click", function() {
                        setTimeout(() => {
                            console.log('Device button clicked');

                            // Select all iframes inside Gutenberg
                            const iframes = document.querySelectorAll('body.block-editor-page iframe');

                            if (iframes.length === 0) {
                                console.error("No iframes found!");
                                return;
                            }

                            // Apply the function to each iframe
                            iframes.forEach(iframe => {
                                // Check if iframe is already loaded
                                if (iframe.contentDocument && iframe.contentDocument.readyState === "complete") {
                                    injectStyleIntoIframe(iframe);
                                } else {
                                    iframe.onload = function () {
                                        injectStyleIntoIframe(iframe);
                                    };
                                }
                            });
                        }, 1000);
                    });
                });
            }, 1000);
        });
    }, 10000);
});







