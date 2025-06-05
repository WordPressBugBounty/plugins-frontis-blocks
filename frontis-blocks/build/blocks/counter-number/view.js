/******/ (() => { // webpackBootstrap
/*!*******************************************!*\
  !*** ./src/blocks/counter-number/view.js ***!
  \*******************************************/
(function () {
  function initCounterAnimation() {
    var counters = document.querySelectorAll('.fb-counter-number');
    counters.forEach(function (counter) {
      if (counter.dataset.animated === "true") return;
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCounter(counter);
            counter.dataset.animated = "true";
            observer.disconnect();
          }
        });
      }, {
        threshold: 0.5
      });
      observer.observe(counter);
    });
  }
  function animateCounter(counterElement) {
    var start = parseFloat(counterElement.getAttribute('counter-start')) || 0;
    var end = parseFloat(counterElement.getAttribute('counter-end')) || 0;
    var duration = parseInt(counterElement.getAttribute('counter-duration'), 10) || 2000;
    var separatorEnabled = counterElement.getAttribute('separator-switch') === 'true';
    var separatorType = counterElement.getAttribute('separator-type') || 'comma';
    var startTime = null;
    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var current = progress * (end - start) + start;
      var formattedNumber;

      // Check if the counter values have decimal places
      var hasDecimal = !Number.isInteger(start) || !Number.isInteger(end);
      var decimalPlaces = hasDecimal ? 1 : 0;
      if (separatorEnabled) {
        switch (separatorType) {
          case 'comma':
            formattedNumber = current.toLocaleString('en-US', {
              minimumFractionDigits: decimalPlaces,
              maximumFractionDigits: decimalPlaces
            });
            break;
          case 'dot':
            formattedNumber = current.toLocaleString('de-DE', {
              minimumFractionDigits: decimalPlaces,
              maximumFractionDigits: decimalPlaces
            });
            break;
          case 'space':
            formattedNumber = current.toLocaleString('fr-FR', {
              minimumFractionDigits: decimalPlaces,
              maximumFractionDigits: decimalPlaces
            }).replace(/\./g, ' ');
            break;
          default:
            formattedNumber = current.toLocaleString(undefined, {
              minimumFractionDigits: decimalPlaces,
              maximumFractionDigits: decimalPlaces
            });
        }
      } else {
        // Only show decimal places if necessary
        formattedNumber = hasDecimal ? current.toFixed(decimalPlaces) : Math.round(current).toString();
      }
      counterElement.textContent = formattedNumber;
      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    }
    window.requestAnimationFrame(step);
  }
  var setupDone = false;
  function setupInitialization() {
    if (setupDone) return;
    setupDone = true;
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(initCounterAnimation, 100);
    } else {
      document.addEventListener("DOMContentLoaded", function () {
        setTimeout(initCounterAnimation, 100);
      });
    }
    if (typeof window !== 'undefined') {
      var lastUrl = window.location.href;
      var urlObserver = new MutationObserver(function () {
        if (lastUrl !== window.location.href) {
          lastUrl = window.location.href;
          setTimeout(initCounterAnimation, 300);
        }
      });
      urlObserver.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }
  setupInitialization();
  window.reinitCounterAnimation = initCounterAnimation;
})();
/******/ })()
;
//# sourceMappingURL=view.js.map