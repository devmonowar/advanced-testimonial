/**
 * Click-to-copy for shortcode helpers in the admin (vanilla JS).
 *
 * @package AdvancedTestimonial
 */
( function () {
	'use strict';

	function flash( el ) {
		var original = el.textContent;
		el.classList.add( 'is-copied' );
		el.textContent = ( window.advancedTestimonialCopy && window.advancedTestimonialCopy.copied ) || 'Copied!';
		window.setTimeout( function () {
			el.textContent = original;
			el.classList.remove( 'is-copied' );
		}, 1200 );
	}

	function copy( text, el ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( function () {
				flash( el );
			} );
			return;
		}

		var area = document.createElement( 'textarea' );
		area.value = text;
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild( area );
		area.select();
		try {
			document.execCommand( 'copy' );
			flash( el );
		} catch ( e ) {} // eslint-disable-line no-empty
		document.body.removeChild( area );
	}

	document.addEventListener( 'click', function ( event ) {
		var el = event.target.closest( '.at-copy' );
		if ( ! el ) {
			return;
		}
		event.preventDefault();
		copy( el.getAttribute( 'data-clipboard' ) || el.textContent, el );
	} );
}() );
