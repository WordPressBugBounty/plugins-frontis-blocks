/******/ (() => { // webpackBootstrap
/*!**************************************!*\
  !*** ./src/blocks/countdown/view.js ***!
  \**************************************/
// document.addEventListener("DOMContentLoaded", () => {
// 	// Find all countdown blocks on the page
// 	const countdownBlocks = document.querySelectorAll("[data-target-date]");

// 	countdownBlocks.forEach((block) => {
// 		const targetDate = block.getAttribute("data-target-date");

// 		if (!targetDate) return;

// 		const daysElem = block.querySelector(".fb-count-down-time.days");
// 		const hoursElem = block.querySelector(".fb-count-down-time.hours");
// 		const minutesElem = block.querySelector(".fb-count-down-time.minutes");
// 		const secondsElem = block.querySelector(".fb-count-down-time.seconds");

// 		const formatTime = (time) => (time < 10 ? `0${time}` : time);

// 		const updateCountdown = () => {
// 			const now = new Date().getTime();
// 			const distance = new Date(targetDate).getTime() - now;

// 			if (distance < 0) {
// 				// Countdown expired
// 				daysElem.textContent = "00";
// 				hoursElem.textContent = "00";
// 				minutesElem.textContent = "00";
// 				secondsElem.textContent = "00";
// 				return;
// 			}

// 			const days = formatTime(Math.floor(distance / (1000 * 60 * 60 * 24)));
// 			const hours = formatTime(
// 				Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
// 			);
// 			const minutes = formatTime(
// 				Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
// 			);
// 			const seconds = formatTime(Math.floor((distance % (1000 * 60)) / 1000));

// 			if (daysElem) daysElem.textContent = days;
// 			if (hoursElem) hoursElem.textContent = hours;
// 			if (minutesElem) minutesElem.textContent = minutes;
// 			if (secondsElem) secondsElem.textContent = seconds;
// 		};

// 		// Update countdown every second
// 		setInterval(updateCountdown, 1000);
// 		updateCountdown();
// 	});
// });

(function () {
  function initCountdown() {
    document.querySelectorAll("[data-target-date]").forEach(function (block) {
      var targetDate = block.getAttribute("data-target-date");
      if (!targetDate) return;
      var daysElem = block.querySelector(".fb-count-down-time.days");
      var hoursElem = block.querySelector(".fb-count-down-time.hours");
      var minutesElem = block.querySelector(".fb-count-down-time.minutes");
      var secondsElem = block.querySelector(".fb-count-down-time.seconds");
      function formatTime(time) {
        return time < 10 ? "0" + time : time;
      }
      function updateCountdown() {
        var now = new Date().getTime();
        var distance = new Date(targetDate).getTime() - now;
        if (distance < 0) {
          // Countdown expired
          daysElem && (daysElem.textContent = "00");
          hoursElem && (hoursElem.textContent = "00");
          minutesElem && (minutesElem.textContent = "00");
          secondsElem && (secondsElem.textContent = "00");
          return;
        }
        var days = formatTime(Math.floor(distance / (1000 * 60 * 60 * 24)));
        var hours = formatTime(Math.floor(distance % (1000 * 60 * 60 * 24) / (1000 * 60 * 60)));
        var minutes = formatTime(Math.floor(distance % (1000 * 60 * 60) / (1000 * 60)));
        var seconds = formatTime(Math.floor(distance % (1000 * 60) / 1000));
        daysElem && (daysElem.textContent = days);
        hoursElem && (hoursElem.textContent = hours);
        minutesElem && (minutesElem.textContent = minutes);
        secondsElem && (secondsElem.textContent = seconds);
      }

      // Update countdown every second
      setInterval(updateCountdown, 1000);
      updateCountdown();
    });
  }
  var setupDone = false;
  function setupInitialization() {
    if (setupDone) return;
    setupDone = true;
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(initCountdown, 100);
    } else {
      document.addEventListener("DOMContentLoaded", function () {
        setTimeout(initCountdown, 100);
      });
    }
    if (typeof window !== 'undefined') {
      var lastUrl = window.location.href;
      var urlObserver = new MutationObserver(function () {
        if (lastUrl !== window.location.href) {
          lastUrl = window.location.href;
          setTimeout(initCountdown, 300);
        }
      });
      urlObserver.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }
  setupInitialization();
  window.reinitCountdown = initCountdown;
})();
/******/ })()
;
//# sourceMappingURL=view.js.map