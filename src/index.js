/**
 * Play Vimeo or YouTube videos in dialog modal
 *
 * @version     2024.9.18
 * @name        CSlice\JS\Play_Video_In_Dialog
 */

window.addEventListener('DOMContentLoaded', function () {
    /**
     * Create dialog element & close button (used below)
     */
    function createDialog() {
        const dialog = document.createElement('dialog');
        dialog.className = 'dialog-modal-video';
        dialog.setAttribute('aria-label', '');
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.setAttribute('tabindex', '-1');

        const closeButton = document.createElement('button');
        closeButton.className = 'dialog-close';
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.addEventListener('click', () => {
            dialog.close();
        });
        dialog.appendChild(closeButton);

        const content = document.createElement('div');
        content.className = 'dialog-content';
        dialog.appendChild(content);

        // Listen for the close event to remove the dialog and stop the video
        dialog.addEventListener('close', () => {
            dialog.remove();
        });

        return { dialog, content };
    }

    /**
     * Play video in modal
     */
    const videoModals = document.querySelectorAll('.open-video-in-modal');
    if (videoModals) {
        videoModals.forEach((videoModal) => {
            const videoModalButton = videoModal;
            const link = videoModalButton.querySelector('a');
            videoModalButton.addEventListener('click', (event) => {
                event.preventDefault();

                const { dialog, content } = createDialog();

                // Use the data-iframe attribute for the video URL
                const videoUrl = link.getAttribute('data-iframe');

                const videoEmbed = document.createElement('iframe');
                videoEmbed.src = videoUrl;
                videoEmbed.frameborder = '0';
                videoEmbed.allow =
                    'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                videoEmbed.allowFullscreen = true;

                content.appendChild(videoEmbed);

                document.body.appendChild(dialog);
                dialog.showModal();
            });
        });
    }
});