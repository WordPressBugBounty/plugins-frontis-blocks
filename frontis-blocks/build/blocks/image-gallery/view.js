/******/ (() => { // webpackBootstrap
/*!******************************************!*\
  !*** ./src/blocks/image-gallery/view.js ***!
  \******************************************/
// document.addEventListener('DOMContentLoaded', () => {
//     const galleryWrappers = document.querySelectorAll('.fb-image-gallery-items');

//     galleryWrappers.forEach((galleryWrapper) => {
//         const blockID = galleryWrapper.getAttribute('data-id');
//         const lightboxLinks = galleryWrapper.querySelectorAll(`.fb-image-gallery-item`);
//         const galleryImages = Array.from(lightboxLinks);
//         let currentIndex = 0;

//         function openLightbox(index) {
//             currentIndex = index;
//             const lightbox = document.createElement('div');
//             lightbox.classList.add('lightbox', blockID);
//             lightbox.innerHTML = `
//                 <div class="lightbox-content">
//                     <button class="lightbox-close">&times;</button>
//                     <div class="lightbox-counter">${currentIndex + 1} / ${galleryImages.length}</div>
//                     <button class="lightbox-prev">&#10094;</button>
//                     <button class="lightbox-next">&#10095;</button>
//                     <div class="lightbox-image-container">
//                         <img src="${galleryImages[currentIndex].getAttribute('href')}" alt="">
//                     </div>
//                     <div class="lightbox-thumbnails">
//                         ${galleryImages.map((img, idx) => `
//                             <img src="${img.getAttribute('href')}" 
//                                  class="thumbnail ${idx === currentIndex ? 'active' : ''}" 
//                                  data-index="${idx}" />
//                         `).join('')}
//                     </div>
//                 </div>
//             `;
//             document.body.appendChild(lightbox);

//             // Add event listeners for lightbox buttons and thumbnails
//             lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
//             lightbox.querySelector('.lightbox-prev').addEventListener('click', prevImage);
//             lightbox.querySelector('.lightbox-next').addEventListener('click', nextImage);
//             lightbox.querySelectorAll('.thumbnail').forEach(thumbnail => {
//                 thumbnail.addEventListener('click', (e) => {
//                     currentIndex = parseInt(e.target.getAttribute('data-index'));
//                     updateLightboxImage();
//                 });
//             });

//             // Add keydown event listener for navigation with arrow keys
//             document.addEventListener('keydown', handleKeyNavigation);

//             function closeLightbox() {
//                 lightbox.remove();
//                 document.removeEventListener('keydown', handleKeyNavigation);
//             }

//             function nextImage() {
//                 currentIndex = (currentIndex + 1) % galleryImages.length;
//                 updateLightboxImage();
//             }

//             function prevImage() {
//                 currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
//                 updateLightboxImage();
//             }

//             function updateLightboxImage() {
//                 const newImage = galleryImages[currentIndex].getAttribute('href');
//                 lightbox.querySelector('.lightbox-image-container img').src = newImage;
//                 lightbox.querySelector('.lightbox-counter').textContent = `${currentIndex + 1} / ${galleryImages.length}`;
//                 lightbox.querySelectorAll('.thumbnail').forEach((thumb, idx) => {
//                     thumb.classList.toggle('active', idx === currentIndex);
//                 });
//             }

//             function handleKeyNavigation(event) {
//                 if (event.key === 'ArrowRight') {
//                     nextImage();
//                 } else if (event.key === 'ArrowLeft') {
//                     prevImage();
//                 } else if (event.key === 'Escape') {
//                     closeLightbox();
//                 }
//             }
//         }

//         galleryImages.forEach((link, index) => {
//             link.addEventListener('click', (event) => {
//                 event.preventDefault();
//                 openLightbox(index);
//             });
//         });
//     });
// });

(function () {
  function initImageGallery() {
    var galleryWrappers = document.querySelectorAll('.fb-image-gallery-items');
    galleryWrappers.forEach(function (galleryWrapper) {
      var blockID = galleryWrapper.getAttribute('data-id');
      var lightboxLinks = galleryWrapper.querySelectorAll(".fb-image-gallery-item");
      var galleryImages = Array.from(lightboxLinks);
      var currentIndex = 0;
      function openLightbox(index) {
        currentIndex = index;
        var lightbox = document.createElement('div');
        lightbox.classList.add('lightbox', blockID);
        lightbox.innerHTML = "\n                    <div class=\"lightbox-content\">\n                        <button class=\"lightbox-close\">&times;</button>\n                        <div class=\"lightbox-counter\">".concat(currentIndex + 1, " / ").concat(galleryImages.length, "</div>\n                        <button class=\"lightbox-prev\">&#10094;</button>\n                        <button class=\"lightbox-next\">&#10095;</button>\n                        <div class=\"lightbox-image-container\">\n                            <img src=\"").concat(galleryImages[currentIndex].getAttribute('href'), "\" alt=\"\">\n                        </div>\n                        <div class=\"lightbox-thumbnails\">\n                            ").concat(galleryImages.map(function (img, idx) {
          return "\n                                    <img src=\"".concat(img.getAttribute('href'), "\" \n                                         class=\"thumbnail ").concat(idx === currentIndex ? 'active' : '', "\" \n                                         data-index=\"").concat(idx, "\" />\n                                ");
        }).join(''), "\n                        </div>\n                    </div>\n                ");
        document.body.appendChild(lightbox);

        // Add event listeners for lightbox buttons and thumbnails
        lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        lightbox.querySelector('.lightbox-prev').addEventListener('click', prevImage);
        lightbox.querySelector('.lightbox-next').addEventListener('click', nextImage);
        lightbox.querySelectorAll('.thumbnail').forEach(function (thumbnail) {
          thumbnail.addEventListener('click', function (e) {
            currentIndex = parseInt(e.target.getAttribute('data-index'));
            updateLightboxImage();
          });
        });

        // Add keydown event listener for navigation with arrow keys
        document.addEventListener('keydown', handleKeyNavigation);
        function closeLightbox() {
          lightbox.remove();
          document.removeEventListener('keydown', handleKeyNavigation);
        }
        function nextImage() {
          currentIndex = (currentIndex + 1) % galleryImages.length;
          updateLightboxImage();
        }
        function prevImage() {
          currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
          updateLightboxImage();
        }
        function updateLightboxImage() {
          var newImage = galleryImages[currentIndex].getAttribute('href');
          lightbox.querySelector('.lightbox-image-container img').src = newImage;
          lightbox.querySelector('.lightbox-counter').textContent = "".concat(currentIndex + 1, " / ").concat(galleryImages.length);
          lightbox.querySelectorAll('.thumbnail').forEach(function (thumb, idx) {
            thumb.classList.toggle('active', idx === currentIndex);
          });
        }
        function handleKeyNavigation(event) {
          if (event.key === 'ArrowRight') {
            nextImage();
          } else if (event.key === 'ArrowLeft') {
            prevImage();
          } else if (event.key === 'Escape') {
            closeLightbox();
          }
        }
      }
      galleryImages.forEach(function (link, index) {
        link.addEventListener('click', function (event) {
          event.preventDefault();
          openLightbox(index);
        });
      });
    });
  }
  var setupDone = false;
  function setupInitialization() {
    if (setupDone) return;
    setupDone = true;
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(initImageGallery, 100);
    } else {
      document.addEventListener("DOMContentLoaded", function () {
        setTimeout(initImageGallery, 100);
      });
    }
    if (typeof window !== 'undefined') {
      var lastUrl = window.location.href;
      var urlObserver = new MutationObserver(function () {
        if (lastUrl !== window.location.href) {
          lastUrl = window.location.href;
          setTimeout(initImageGallery, 300);
        }
      });
      urlObserver.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }
  setupInitialization();
  window.reinitImageGallery = initImageGallery;
})();
/******/ })()
;
//# sourceMappingURL=view.js.map