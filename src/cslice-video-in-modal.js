/**
 * Play Vimeo or YouTube videos from posts in dialog modal
 *
 * @version     2023.12.1
 * @name        CSlice\JS\Play_Video_In_Dialog
 */

window.addEventListener( 'DOMContentLoaded', function () {
	/**
	 * Create dialog element & close button (used below)
	 */
	function createDialog() {
		const dialog = document.createElement( 'dialog' );
		dialog.className = 'dialog-modal-video';

		const closeButton = document.createElement( 'button' );
		closeButton.className = 'dialog-close';
		closeButton.innerHTML = '&#215;';
		closeButton.addEventListener( 'click', () => {
			dialog.close();
			dialog.classList.add( 'dialog-closed' );
		} );
		dialog.appendChild( closeButton );

		return dialog;
	}

	/**
	 * Play video in modal
	 */
	const videoModals = document.querySelectorAll( '.open-video-in-modal' );
	if ( videoModals ) {
		videoModals.forEach( ( videoModal ) => {
			const videoModalButton = videoModal;
			const link = videoModalButton.querySelector('a');
			videoModalButton.addEventListener( 'click', ( event ) => {
				event.preventDefault();

				const videoModalContent = document.createElement( 'div' );
				videoModalContent.className = 'dialog-content';

				// Vimeo & YouTube URL parameters
				let videoUrl = link.href;
				console.log('videoUrl', videoUrl);
				if ( videoUrl && videoUrl.includes( 'vimeo.com' ) ) {
					const videoId = videoUrl.split( '/' ).pop();
					videoUrl = `https://player.vimeo.com/video/${ videoId }?autoplay=1`;
				}
				if ( videoUrl && videoUrl.includes( 'youtube.com' ) ) {
					const videoId = new URL( videoUrl ).searchParams.get( 'v' );
					videoUrl = `https://www.youtube.com/embed/${ videoId }?modestbranding=1&autoplay=1&rel=0`;
				}

				const videoEmbed = document.createElement( 'iframe' );
				videoEmbed.src = videoUrl;
				videoEmbed.frameborder = '0';
				videoEmbed.allow =
					'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
				videoEmbed.allowFullscreen = true;

				videoModalContent.appendChild( videoEmbed );

				const dialog = createDialog();
				dialog.appendChild( videoModalContent );
				document.body.appendChild( dialog );

				dialog.showModal();
				videoEmbed.focus();

				// Close dialog on click outside
				dialog.addEventListener( 'click', () => {
					dialog.remove();
				} );
			} );
		} );
	}
} );
