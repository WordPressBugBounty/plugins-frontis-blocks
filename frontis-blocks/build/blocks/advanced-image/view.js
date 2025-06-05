/******/ (() => { // webpackBootstrap
/*!*******************************************!*\
  !*** ./src/blocks/advanced-image/view.js ***!
  \*******************************************/
(function () {
  // Core lazy-load initializer
  function initLazyLoad() {
    // grab the first wrapper (or narrow down with a more specific selector)
    var wrapper = document.querySelector('.fb-advanced-image-wrapper');
    var switcherValue = wrapper.dataset.smartLazyLoadSwitcher;
    if (!switcherValue) {
      return;
    }

    // Modern browsers with IntersectionObserver
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var img = entry.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
              img.removeAttribute('data-src');
            }
            if (img.dataset.srcset) {
              img.srcset = img.dataset.srcset;
              img.removeAttribute('data-srcset');
            }
            observer.unobserve(img);
          }
        });
      });
      document.querySelectorAll('img[data-src]').forEach(function (img) {
        observer.observe(img);
      });
    } else {
      // Fallback for older browsers
      window.addEventListener('load', function () {
        document.querySelectorAll('img[data-src]').forEach(function (img) {
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          if (img.dataset.srcset) {
            img.srcset = img.dataset.srcset;
            img.removeAttribute('data-srcset');
          }
        });
      });
    }
  }

  // Set up lazy-load on initial page load and observe dynamic content
  function setupLazyLoad() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initLazyLoad);
    } else {
      initLazyLoad();
    }

    // Watch for new images added to the DOM
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.addedNodes.length) {
          initLazyLoad();
        }
      });
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  // Expose a global re-initializer
  window.reinitLazyLoad = initLazyLoad;

  // Kick things off
  setupLazyLoad();
})();
/******/ })()
;
//# sourceMappingURL=view.js.map