/******/ (() => { // webpackBootstrap
/*!***********************************!*\
  !*** ./src/blocks/slider/view.js ***!
  \***********************************/
(function () {
  function initSliders() {
    // Select all slider containers
    document.querySelectorAll('.fb-slider-container').forEach(function (sliderContainer) {
      var blockId = sliderContainer.dataset.blockId;
      if (!blockId) return;
      var swiperSettings = sliderContainer.dataset.swiperSettings;
      if (!swiperSettings) return;
      var sliderOptions = JSON.parse(swiperSettings);

      // Initialize Swiper for the current slider
      new Swiper(sliderContainer.querySelector('.fb-swiper'), sliderOptions);
    });
  }
  var setupDone = false;
  function setupInitialization() {
    if (setupDone) return;
    setupDone = true;
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(initSliders, 100);
    } else {
      document.addEventListener('DOMContentLoaded', function () {
        setTimeout(initSliders, 100);
      });
    }
    if (typeof window !== 'undefined') {
      var lastUrl = window.location.href;
      var urlObserver = new MutationObserver(function () {
        if (lastUrl !== window.location.href) {
          lastUrl = window.location.href;
          setTimeout(initSliders, 300);
        }
      });
      urlObserver.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }
  setupInitialization();

  // If needed, expose re-initialization function
  window.reinitSliders = initSliders;
})();
/******/ })()
;
//# sourceMappingURL=view.js.map