/******/ (() => { // webpackBootstrap
/*!****************************************!*\
  !*** ./src/blocks/progressbar/view.js ***!
  \****************************************/
(function () {
  function initAccordion() {
    var accordionItems = document.querySelectorAll(".fb-accordion-item-wrapper");
    accordionItems.forEach(function (itemWrapper) {
      var titleWrapper = itemWrapper.querySelector(".fb-accordion-item-title-wrapper");
      var contentWrapper = itemWrapper.querySelector(".fb-accordion-content-wrapper");

      // Prevent multiple initializations
      if (titleWrapper.hasAttribute('data-accordion-initialized')) return;
      titleWrapper.setAttribute('data-accordion-initialized', 'true');
      titleWrapper.addEventListener("click", function (event) {
        event.stopPropagation();

        // Check current state
        var isActive = titleWrapper.classList.contains("active");

        // Reset all accordions at the same level
        var parentAccordion = itemWrapper.closest(".fb-accordion-content-wrapper") || document.body;
        var siblingItems = parentAccordion.querySelectorAll(".fb-accordion-item-wrapper");
        siblingItems.forEach(function (sibling) {
          if (sibling !== itemWrapper) {
            var siblingTitle = sibling.querySelector(".fb-accordion-item-title-wrapper");
            var siblingContent = sibling.querySelector(".fb-accordion-content-wrapper");
            if (siblingTitle) siblingTitle.classList.remove("active");
            if (siblingContent) {
              siblingContent.classList.remove("active");
              siblingContent.style.maxHeight = "0px";
            }
          }
        });

        // Toggle current accordion
        titleWrapper.classList.toggle("active", !isActive);
        contentWrapper.classList.toggle("active", !isActive);

        // Set max height
        if (!isActive) {
          contentWrapper.style.maxHeight = "".concat(contentWrapper.scrollHeight, "px");
        } else {
          contentWrapper.style.maxHeight = "0px";
        }
      });
    });
  }

  // Initialize on different loading states
  function setupAccordion() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initAccordion);
    } else {
      initAccordion();
    }

    // Handle dynamic content changes
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.addedNodes.length || mutation.removedNodes.length) {
          initAccordion();
        }
      });
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  // Expose initialization method globally
  window.reinitAccordion = initAccordion;

  // Start setup
  setupAccordion();
})();
/******/ })()
;
//# sourceMappingURL=view.js.map