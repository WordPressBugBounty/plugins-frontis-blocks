/******/ (() => { // webpackBootstrap
/*!*********************************************!*\
  !*** ./src/blocks/marquee-carousel/view.js ***!
  \*********************************************/
// document.addEventListener('DOMContentLoaded', () => {
//     const initializeSliders = () => {
//         const sliders = document.querySelectorAll('.fb-marquee-carousel-container');

//         sliders.forEach((sliderContainer) => {
//             try {
//                 const swiperSettings = sliderContainer.dataset.swiperSettings;
//                 const slideWidth = sliderContainer.dataset.slideWidth;
//                 if (swiperSettings) {
//                     const settings = JSON.parse(swiperSettings);

//                     settings.on = {
//                         init: function () {
//                             const slides = sliderContainer.querySelectorAll('.swiper-slide');
//                             slides.forEach(slide => {
//                                 slide.style.width = slideWidth;
//                             });
//                         }
//                     };

//                     new Swiper(sliderContainer.querySelector('.swiper'), settings);
//                 }
//             } catch (error) {
//                 console.error('Error initializing slider:', error);
//             }
//         });
//     };

//     initializeSliders();

//     if (window.wp && window.wp.hooks) {
//         window.wp.hooks.addAction('blocks.blockRendered', 'frontis-marquee-carousel', () => {
//             initializeSliders();
//         });
//     }
// });

(function () {
  function initMarqueeCarousels() {
    var sliders = document.querySelectorAll('.fb-marquee-carousel-container');
    sliders.forEach(function (sliderContainer) {
      // Prevent multiple initializations
      if (sliderContainer.hasAttribute('data-carousel-initialized')) return;
      sliderContainer.setAttribute('data-carousel-initialized', 'true');
      try {
        var swiperSettings = sliderContainer.dataset.swiperSettings;
        var slideWidth = sliderContainer.dataset.slideWidth;
        if (swiperSettings) {
          var settings = JSON.parse(swiperSettings);
          settings.on = {
            init: function init() {
              var slides = sliderContainer.querySelectorAll('.swiper-slide');
              slides.forEach(function (slide) {
                slide.style.width = slideWidth;
              });
            }
          };
          new Swiper(sliderContainer.querySelector('.swiper'), settings);
        }
      } catch (error) {
        console.error('Error initializing slider:', error);
      }
    });
  }

  // Initialize on different loading states
  function setupMarqueeCarousels() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initMarqueeCarousels);
    } else {
      initMarqueeCarousels();
    }

    // Handle dynamic content changes
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.addedNodes.length || mutation.removedNodes.length) {
          initMarqueeCarousels();
        }
      });
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    // Support WordPress hooks if available
    if (window.wp && window.wp.hooks) {
      window.wp.hooks.addAction('blocks.blockRendered', 'frontis-marquee-carousel', initMarqueeCarousels);
    }
  }

  // Expose initialization method globally
  window.reinitMarqueeCarousels = initMarqueeCarousels;

  // Start setup
  setupMarqueeCarousels();
})();
/******/ })()
;
//# sourceMappingURL=view.js.map