"use strict";

/**
 * Play Vimeo or YouTube videos from posts in dialog modal
 *
 * @version     2023.12.1
 * @name        CSlice\JS\Play_Video_In_Dialog
 */

window.addEventListener('DOMContentLoaded', function () {
  /**
   * Create dialog element & close button (used below)
   */
  function createDialog() {
    var dialog = document.createElement('dialog');
    dialog.className = 'dialog-modal-video';
    var closeButton = document.createElement('button');
    closeButton.className = 'dialog-close';
    closeButton.innerHTML = '&#215;';
    closeButton.addEventListener('click', function () {
      dialog.close();
      dialog.classList.add('dialog-closed');
    });
    dialog.appendChild(closeButton);
    return dialog;
  }

  /**
   * Play video in modal
   */
  var videoModals = document.querySelectorAll('.open-video-in-modal');
  if (videoModals) {
    videoModals.forEach(function (videoModal) {
      var videoModalButton = videoModal;
      var link = videoModalButton.querySelector('a');
      videoModalButton.addEventListener('click', function (event) {
        event.preventDefault();
        var videoModalContent = document.createElement('div');
        videoModalContent.className = 'dialog-content';

        // Vimeo & YouTube URL parameters
        var videoUrl = link.href;
        console.log('videoUrl', videoUrl);
        if (videoUrl && videoUrl.includes('vimeo.com')) {
          var videoId = videoUrl.split('/').pop();
          videoUrl = "https://player.vimeo.com/video/".concat(videoId, "?autoplay=1");
        }
        if (videoUrl && videoUrl.includes('youtube.com')) {
          var _videoId = new URL(videoUrl).searchParams.get('v');
          videoUrl = "https://www.youtube.com/embed/".concat(_videoId, "?modestbranding=1&autoplay=1&rel=0");
        }
        var videoEmbed = document.createElement('iframe');
        videoEmbed.src = videoUrl;
        videoEmbed.frameborder = '0';
        videoEmbed.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        videoEmbed.allowFullscreen = true;
        videoModalContent.appendChild(videoEmbed);
        var dialog = createDialog();
        dialog.appendChild(videoModalContent);
        document.body.appendChild(dialog);
        dialog.showModal();
        videoEmbed.focus();

        // Close dialog on click outside
        dialog.addEventListener('click', function () {
          dialog.remove();
        });
      });
    });
  }
});