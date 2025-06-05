/******/ (() => { // webpackBootstrap
/*!*******************************************!*\
  !*** ./src/blocks/advanced-video/view.js ***!
  \*******************************************/
(function () {
  function initVideoPlayer() {
    var container = document.getElementById('fb-player-option');
    var playIconSpan = document.querySelector('.fb-play-icon');
    if (container) {
      var url = container.getAttribute('data-url');
      var autoPlay = container.getAttribute('data-autoplay') === 'true';
      var muted = container.getAttribute('data-muted') === 'true';
      var loop = container.getAttribute('data-loop') === 'true';
      var controls = container.getAttribute('data-controls') === 'true';
      var previewImage = container.getAttribute('data-preview-image');
      var playIcon = container.getAttribute('data-play-icon');
      renderReactPlayer(container, {
        url: url,
        playing: autoPlay,
        muted: muted,
        loop: loop,
        controls: controls,
        light: previewImage,
        onClickPreview: function onClickPreview() {
          container.setAttribute('data-autoplay', 'true');
          if (playIconSpan) {
            playIconSpan.style.display = 'none';
          }
          renderReactPlayer(container, {
            url: url,
            playing: true,
            muted: muted,
            loop: loop,
            controls: controls,
            light: previewImage,
            width: '100%',
            height: '100%',
            className: 'fb-react-player'
          });
        },
        width: '100%',
        height: '100%',
        className: 'fb-react-player'
      });
      if (playIconSpan) {
        playIconSpan.addEventListener('click', function () {
          var previewElement = container.querySelector('.react-player__preview');
          if (previewElement) {
            previewElement.click();
          }
        });
      }
    }
  }
  var setupDone = false;
  function setupInitialization() {
    if (setupDone) return;
    setupDone = true;
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(initVideoPlayer, 100);
    } else {
      document.addEventListener('DOMContentLoaded', function () {
        setTimeout(initVideoPlayer, 100);
      });
    }
    if (typeof window !== 'undefined') {
      var lastUrl = window.location.href;
      var urlObserver = new MutationObserver(function () {
        if (lastUrl !== window.location.href) {
          lastUrl = window.location.href;
          setTimeout(initVideoPlayer, 300);
        }
      });
      urlObserver.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }
  setupInitialization();
  window.reinitVideoPlayer = initVideoPlayer;
})();
/******/ })()
;
//# sourceMappingURL=view.js.map