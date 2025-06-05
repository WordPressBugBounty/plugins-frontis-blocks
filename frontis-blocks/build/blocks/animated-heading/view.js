/******/ (() => { // webpackBootstrap
/*!*********************************************!*\
  !*** ./src/blocks/animated-heading/view.js ***!
  \*********************************************/
(function () {
  function initAnimatedHeading() {
    var containers = document.querySelectorAll('.fb-animated-heading-container');
    containers.forEach(function (container) {
      var animationType = container.getAttribute('data-animation-type');

      // Handle Typing Animation
      if (animationType === 'typing') {
        var _typeEffect = function typeEffect() {
          var currentText = items[currentItem];
          var displayText = currentText.substring(0, charIndex);
          typingContainer.textContent = displayText;
          if (!isDeleting) {
            if (charIndex < currentText.length) {
              charIndex++;
              setTimeout(_typeEffect, 100);
            } else {
              isDeleting = true;
              setTimeout(_typeEffect, 1000);
            }
          } else {
            if (charIndex > 0) {
              charIndex--;
              setTimeout(_typeEffect, 50);
            } else {
              isDeleting = false;
              currentItem = (currentItem + 1) % items.length;
              setTimeout(_typeEffect, 500);
            }
          }
        };
        var typingContainer = container.querySelector('.fb-animated-headings');
        if (!typingContainer) return;
        var itemElements = container.querySelectorAll('.fb-animated-heading-item');
        var items = Array.from(itemElements).map(function (el) {
          return el.textContent;
        }).filter(Boolean);
        if (!items.length) return;
        itemElements.forEach(function (el) {
          return el.style.display = 'none';
        }); // hide original

        var currentItem = 0,
          charIndex = 0,
          isDeleting = false;
        _typeEffect();
      }

      // Handle Swirl Animation
      if (animationType === 'swirl') {
        var createWordSpan = function createWordSpan(text) {
          var isActive = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
          var wrapper = document.createElement('span');
          wrapper.className = 'fb-animated-heading-dynamic-text';
          if (isActive) wrapper.classList.add('fb-animated-heading-text-active');
          text.split('').forEach(function (_char) {
            var letter = document.createElement('span');
            letter.className = 'fb-animated-heading-dynamic-letter';
            letter.textContent = _char === ' ' ? "\xA0" : _char;
            wrapper.appendChild(letter);
          });
          return wrapper;
        };
        var showNextWord = function showNextWord() {
          var oldWord = containerWrapper.querySelector('.fb-animated-heading-text-active');
          var newWord = createWordSpan(texts[currentIndex], true);
          containerWrapper.appendChild(newWord);
          var newLetters = newWord.querySelectorAll('.fb-animated-heading-dynamic-letter');
          var totalWidthSubPixel = Array.from(newLetters).reduce(function (sum, letter) {
            return sum + letter.getBoundingClientRect().width;
          }, 0);
          var swirlWrapper = document.querySelector('.fb-animated-heading-swirl-wrapper');
          if (swirlWrapper) {
            swirlWrapper.style.width = "".concat(totalWidthSubPixel, "px");
          }
          newLetters.forEach(function (l, i) {
            setTimeout(function () {
              l.classList.add('fb-animated-heading-animation-in');
            }, i * 30);
          });
          if (oldWord) {
            oldWord.classList.remove('fb-animated-heading-text-active');
            var oldLetters = oldWord.querySelectorAll('.fb-animated-heading-dynamic-letter');
            oldLetters.forEach(function (l, i) {
              setTimeout(function () {
                l.classList.add('fb-animated-heading-animation-out');
              }, i * 30);
            });
            setTimeout(function () {
              oldWord.remove();
            }, oldLetters.length * 30 + 500);
          }
          currentIndex = (currentIndex + 1) % texts.length;
        };
        var texts = Array.from(container.querySelectorAll('.fb-animated-heading-item')).map(function (item) {
          return item.textContent;
        }).filter(Boolean);
        if (texts.length === 0) return;
        var currentIndex = 0;
        var containerWrapper = container.querySelector('#animated-heading-wrapper');
        if (!containerWrapper) return;
        containerWrapper.innerHTML = '';
        var firstWord = createWordSpan(texts[currentIndex], true);
        containerWrapper.appendChild(firstWord);
        var firstLetters = firstWord.querySelectorAll('.fb-animated-heading-dynamic-letter');
        firstLetters.forEach(function (l, i) {
          setTimeout(function () {
            l.classList.add('fb-animated-heading-animation-in');
          }, i * 30);
        });
        currentIndex = (currentIndex + 1) % texts.length;
        setInterval(showNextWord, 3000);
      }
    });
  }
  function setupAnimatedHeading() {
    // Init on load
    window.addEventListener('load', initAnimatedHeading);

    // Dynamic reload (in case of AJAX/Gutenberg preview)
    var observer = new MutationObserver(function () {
      initAnimatedHeading();
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  // Expose for external reload
  window.reinitAnimatedHeading = initAnimatedHeading;
  setupAnimatedHeading();
})();
/******/ })()
;
//# sourceMappingURL=view.js.map