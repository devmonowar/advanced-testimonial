/**
 * Advanced Testimonial — video testimonial lightbox.
 *
 * Nothing external loads on page render: the YouTube/Vimeo iframe (or the
 * <video> element for self-hosted files) is only created when the visitor
 * clicks a play button, and is destroyed again when the lightbox closes.
 */
( function () {
	'use strict';

	var modal = null;
	var lastFocus = null;

	function buildModal() {
		if ( modal ) {
			return modal;
		}

		modal = document.createElement( 'div' );
		modal.className = 'at-video-modal';
		modal.setAttribute( 'role', 'dialog' );
		modal.setAttribute( 'aria-modal', 'true' );
		modal.setAttribute( 'aria-label', 'Video testimonial' );
		modal.hidden = true;
		modal.innerHTML =
			'<div class="at-video-modal__backdrop"></div>' +
			'<div class="at-video-modal__dialog">' +
				'<button type="button" class="at-video-modal__close" aria-label="Close">' +
					'<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 6.4 17.6 5 12 10.6 6.4 5 5 6.4 10.6 12 5 17.6 6.4 19l5.6-5.6 5.6 5.6 1.4-1.4L13.4 12z"/></svg>' +
				'</button>' +
				'<div class="at-video-modal__frame"></div>' +
			'</div>';

		document.body.appendChild( modal );

		modal.querySelector( '.at-video-modal__backdrop' ).addEventListener( 'click', close );
		modal.querySelector( '.at-video-modal__close' ).addEventListener( 'click', close );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! modal.hidden ) {
				close();
			}
		} );

		return modal;
	}

	function open( type, src, trigger ) {
		var m = buildModal();
		var frame = m.querySelector( '.at-video-modal__frame' );

		if ( 'file' === type ) {
			var video = document.createElement( 'video' );
			video.controls = true;
			video.autoplay = true;
			video.playsInline = true;
			video.src = src;
			frame.appendChild( video );
		} else {
			var iframe = document.createElement( 'iframe' );
			iframe.src = src;
			iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
			iframe.setAttribute( 'allowfullscreen', '' );
			iframe.setAttribute( 'title', 'Video testimonial' );
			frame.appendChild( iframe );
		}

		lastFocus = trigger;
		m.hidden = false;
		document.body.classList.add( 'at-video-modal-open' );
		m.querySelector( '.at-video-modal__close' ).focus();
	}

	function close() {
		if ( ! modal || modal.hidden ) {
			return;
		}

		modal.hidden = true;
		document.body.classList.remove( 'at-video-modal-open' );
		// Destroy the player so audio stops and nothing keeps loading.
		modal.querySelector( '.at-video-modal__frame' ).innerHTML = '';

		if ( lastFocus && document.contains( lastFocus ) ) {
			lastFocus.focus();
		}
		lastFocus = null;
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest ? e.target.closest( '.at-video__play' ) : null;
		if ( ! btn ) {
			return;
		}

		var wrap = btn.closest( '.at-video' );
		if ( ! wrap ) {
			return;
		}

		var type = wrap.getAttribute( 'data-at-video-type' );
		var src = wrap.getAttribute( 'data-at-video-src' );
		if ( ! type || ! src ) {
			return;
		}

		e.preventDefault();
		open( type, src, btn );
	} );
}() );
