/**
 * Admin scripts — company logo media picker (vanilla JS, no jQuery).
 *
 * @package AdvancedTestimonial
 */
( function () {
	'use strict';

	var l10n = window.advancedTestimonialAdmin || {};

	function initMediaField( wrap ) {
		var selectBtn = wrap.querySelector( '.at-media-select' );
		var removeBtn = wrap.querySelector( '.at-media-remove' );
		var input     = wrap.querySelector( 'input[type="hidden"]' );
		var preview   = wrap.querySelector( '.at-media-preview' );
		var frame;

		if ( ! selectBtn || ! input || ! window.wp || ! window.wp.media ) {
			return;
		}

		selectBtn.addEventListener( 'click', function ( event ) {
			event.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = window.wp.media( {
				title: l10n.mediaTitle || 'Select image',
				button: { text: l10n.mediaButton || 'Use image' },
				library: { type: 'image' },
				multiple: false
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var size = ( attachment.sizes && attachment.sizes.thumbnail ) ? attachment.sizes.thumbnail.url : attachment.url;

				input.value = attachment.id;

				var img = document.createElement( 'img' );
				img.className = 'at-media-preview-img';
				img.alt = '';
				img.src = size;
				preview.innerHTML = '';
				preview.appendChild( img );

				if ( removeBtn ) {
					removeBtn.style.display = '';
				}
			} );

			frame.open();
		} );

		if ( removeBtn ) {
			removeBtn.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				input.value = '';
				preview.innerHTML = '';
				removeBtn.style.display = 'none';
			} );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var fields = document.querySelectorAll( '.at-media-field' );
		Array.prototype.forEach.call( fields, initMediaField );
	} );
}() );
