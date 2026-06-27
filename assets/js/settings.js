/**
 * Settings page — tab switching and color swatch sync (vanilla JS).
 *
 * @package AdvancedTestimonial
 */
( function () {
	'use strict';

	function initTabs() {
		var tabs   = document.querySelectorAll( '.at-settings-tabs .nav-tab' );
		var panels = document.querySelectorAll( '.at-settings-panel' );

		if ( ! tabs.length ) {
			return;
		}

		function activate( tabKey ) {
			Array.prototype.forEach.call( tabs, function ( tab ) {
				var on = tab.getAttribute( 'data-tab' ) === tabKey;
				tab.classList.toggle( 'nav-tab-active', on );
			} );

			Array.prototype.forEach.call( panels, function ( panel ) {
				var on = panel.getAttribute( 'data-panel' ) === tabKey;
				panel.style.display = on ? '' : 'none';
			} );

			// The "Save Changes" button only applies to the settings tabs.
			var save = document.querySelector( '.at-settings-save' );
			if ( save ) {
				save.style.display = ( 'tools' === tabKey ) ? 'none' : '';
			}
		}

		Array.prototype.forEach.call( tabs, function ( tab ) {
			tab.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				var key = tab.getAttribute( 'data-tab' );
				activate( key );
				if ( window.history && window.history.replaceState ) {
					window.history.replaceState( null, '', '#' + key );
				}
			} );
		} );

		var hash = window.location.hash.replace( '#', '' );
		if ( hash ) {
			var match = document.querySelector( '.at-settings-tabs .nav-tab[data-tab="' + hash + '"]' );
			if ( match ) {
				activate( hash );
			}
		}
	}

	function initColors() {
		var swatches = document.querySelectorAll( '.at-color-swatch' );

		Array.prototype.forEach.call( swatches, function ( swatch ) {
			var field = document.getElementById( swatch.getAttribute( 'data-target' ) );
			if ( ! field ) {
				return;
			}

			swatch.addEventListener( 'input', function () {
				field.value = swatch.value;
			} );

			field.addEventListener( 'input', function () {
				if ( /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test( field.value ) ) {
					swatch.value = field.value;
				}
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initTabs();
		initColors();
	} );
}() );
