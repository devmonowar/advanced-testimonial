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

		function store( key ) {
			try {
				window.sessionStorage.setItem( 'atSettingsTab', key );
			} catch ( e ) {}
		}

		Array.prototype.forEach.call( tabs, function ( tab ) {
			tab.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				var key = tab.getAttribute( 'data-tab' );
				activate( key );
				store( key );
				if ( window.history && window.history.replaceState ) {
					window.history.replaceState( null, '', '#' + key );
				}
			} );
		} );

		// Remember the active tab across the Save round-trip: options.php
		// redirects back without the hash, which would otherwise reset to the
		// first (General) tab.
		var form = document.querySelector( 'form[action="options.php"]' );
		if ( form ) {
			form.addEventListener( 'submit', function () {
				var active = document.querySelector( '.at-settings-tabs .nav-tab.nav-tab-active' );
				if ( active ) {
					store( active.getAttribute( 'data-tab' ) );
				}
			} );
		}

		var hash = window.location.hash.replace( '#', '' );
		if ( hash && document.querySelector( '.at-settings-tabs .nav-tab[data-tab="' + hash + '"]' ) ) {
			activate( hash );
			return;
		}

		// Just saved: restore the tab the change was made on.
		if ( window.location.search.indexOf( 'settings-updated' ) !== -1 ) {
			var saved = null;
			try {
				saved = window.sessionStorage.getItem( 'atSettingsTab' );
			} catch ( e ) {}
			if ( saved && document.querySelector( '.at-settings-tabs .nav-tab[data-tab="' + saved + '"]' ) ) {
				activate( saved );
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
