/**
 * Demo Library — client-side search and category filtering (vanilla JS).
 *
 * @package AdvancedTestimonial
 */
( function () {
	'use strict';

	var wrap = document.querySelector( '.at-demo-library' );
	if ( ! wrap ) {
		return;
	}

	var search  = wrap.querySelector( '.at-demo-search' );
	var buttons = Array.prototype.slice.call( wrap.querySelectorAll( '.at-demo-filters [data-cat]' ) );
	var cards   = Array.prototype.slice.call( wrap.querySelectorAll( '.at-demo-card' ) );
	var empty   = wrap.querySelector( '.at-demo-empty' );
	var current = '*';

	function apply() {
		var query = ( search ? search.value : '' ).trim().toLowerCase();
		var shown = 0;

		cards.forEach( function ( card ) {
			var okCat   = '*' === current || card.getAttribute( 'data-category' ) === current;
			var okQuery = '' === query || ( card.getAttribute( 'data-search' ) || '' ).indexOf( query ) !== -1;
			var show    = okCat && okQuery;

			card.style.display = show ? '' : 'none';
			if ( show ) {
				shown++;
			}
		} );

		if ( empty ) {
			empty.hidden = 0 !== shown;
		}
	}

	if ( search ) {
		search.addEventListener( 'input', apply );
	}

	buttons.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			current = btn.getAttribute( 'data-cat' );
			buttons.forEach( function ( other ) {
				var active = other === btn;
				other.classList.toggle( 'button-primary', active );
				other.classList.toggle( 'is-active', active );
			} );
			apply();
		} );
	} );
}() );
