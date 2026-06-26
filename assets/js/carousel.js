/**
 * Advanced Testimonial — lightweight vanilla-JS carousel.
 *
 * No jQuery, no external libraries. Handles responsive items-per-view,
 * prev/next, dots, keyboard, touch swipe and optional autoplay.
 *
 * @package AdvancedTestimonial
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function Carousel( root ) {
		this.root     = root;
		this.track    = root.querySelector( '.at-carousel__track' );
		this.slides   = Array.prototype.slice.call( root.querySelectorAll( '.at-carousel__slide' ) );
		this.prevBtn  = root.querySelector( '.at-carousel__prev' );
		this.nextBtn  = root.querySelector( '.at-carousel__next' );
		this.dotsWrap = root.querySelector( '.at-carousel__dots' );
		this.columns  = parseInt( root.getAttribute( 'data-columns' ), 10 ) || 1;
		this.autoplay = parseInt( root.getAttribute( 'data-autoplay' ), 10 ) || 0;
		this.index    = 0;
		this.timer    = null;

		if ( ! this.track || this.slides.length === 0 ) {
			return;
		}

		this.bind();
		this.layout();
		this.startAutoplay();
	}

	Carousel.prototype.perView = function () {
		var width = window.innerWidth;
		var pv    = this.columns;

		if ( width <= 600 ) {
			pv = 1;
		} else if ( width <= 900 ) {
			pv = Math.min( 2, this.columns );
		}

		return Math.max( 1, Math.min( pv, this.slides.length ) );
	};

	Carousel.prototype.pages = function () {
		return Math.ceil( this.slides.length / this.perView() );
	};

	Carousel.prototype.layout = function () {
		var pv = this.perView();
		var basis = ( 100 / pv ) + '%';

		this.slides.forEach( function ( slide ) {
			slide.style.flex     = '0 0 ' + basis;
			slide.style.maxWidth = basis;
		} );

		if ( this.index > this.pages() - 1 ) {
			this.index = this.pages() - 1;
		}

		this.buildDots();
		this.update();
	};

	Carousel.prototype.update = function () {
		this.track.style.transform = 'translateX(-' + ( this.index * 100 ) + '%)';

		if ( this.dotsWrap ) {
			Array.prototype.forEach.call( this.dotsWrap.children, function ( dot, i ) {
				var active = i === this.index;
				dot.classList.toggle( 'is-active', active );
				dot.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				dot.tabIndex = active ? 0 : -1;
			}.bind( this ) );
		}
	};

	Carousel.prototype.goTo = function ( page ) {
		var total = this.pages();
		this.index = ( page + total ) % total;
		this.update();
	};

	Carousel.prototype.next = function () {
		this.goTo( this.index + 1 );
	};

	Carousel.prototype.prev = function () {
		this.goTo( this.index - 1 );
	};

	Carousel.prototype.buildDots = function () {
		if ( ! this.dotsWrap ) {
			return;
		}

		this.dotsWrap.innerHTML = '';
		var total = this.pages();

		if ( total <= 1 ) {
			return;
		}

		for ( var i = 0; i < total; i++ ) {
			var dot = document.createElement( 'button' );
			dot.type = 'button';
			dot.className = 'at-carousel__dot';
			dot.setAttribute( 'role', 'tab' );
			dot.setAttribute( 'aria-label', 'Slide ' + ( i + 1 ) );
			dot.addEventListener( 'click', this.goTo.bind( this, i ) );
			this.dotsWrap.appendChild( dot );
		}
	};

	Carousel.prototype.bind = function () {
		if ( this.nextBtn ) {
			this.nextBtn.addEventListener( 'click', this.next.bind( this ) );
		}
		if ( this.prevBtn ) {
			this.prevBtn.addEventListener( 'click', this.prev.bind( this ) );
		}

		this.root.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'ArrowLeft' ) {
				this.prev();
			} else if ( event.key === 'ArrowRight' ) {
				this.next();
			}
		}.bind( this ) );

		// Touch swipe.
		var startX = 0;
		var dragging = false;
		var viewport = this.root.querySelector( '.at-carousel__viewport' ) || this.root;

		viewport.addEventListener( 'touchstart', function ( e ) {
			startX = e.touches[ 0 ].clientX;
			dragging = true;
		}, { passive: true } );

		viewport.addEventListener( 'touchend', function ( e ) {
			if ( ! dragging ) {
				return;
			}
			dragging = false;
			var delta = e.changedTouches[ 0 ].clientX - startX;
			if ( Math.abs( delta ) > 40 ) {
				if ( delta < 0 ) {
					this.next();
				} else {
					this.prev();
				}
			}
		}.bind( this ), { passive: true } );

		// Pause autoplay on interaction.
		this.root.addEventListener( 'mouseenter', this.stopAutoplay.bind( this ) );
		this.root.addEventListener( 'mouseleave', this.startAutoplay.bind( this ) );
		this.root.addEventListener( 'focusin', this.stopAutoplay.bind( this ) );

		var resizeTimer;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( this.layout.bind( this ), 150 );
		}.bind( this ) );
	};

	Carousel.prototype.startAutoplay = function () {
		if ( ! this.autoplay || reduceMotion || this.pages() <= 1 ) {
			return;
		}
		this.stopAutoplay();
		this.timer = window.setInterval( this.next.bind( this ), this.autoplay * 1000 );
	};

	Carousel.prototype.stopAutoplay = function () {
		if ( this.timer ) {
			window.clearInterval( this.timer );
			this.timer = null;
		}
	};

	function init() {
		var carousels = document.querySelectorAll( '[data-at-carousel]' );
		Array.prototype.forEach.call( carousels, function ( root ) {
			if ( ! root.getAttribute( 'data-at-ready' ) ) {
				root.setAttribute( 'data-at-ready', '1' );
				new Carousel( root );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
