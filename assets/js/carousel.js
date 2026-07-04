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

		// Label each slide "N of M" for screen readers.
		var total = this.slides.length;
		this.slides.forEach( function ( slide, i ) {
			slide.setAttribute( 'aria-label', ( i + 1 ) + ' of ' + total );
		} );

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

	// Highest first-visible index — the carousel steps one item at a time, so
	// the last position shows the final "perView" slides.
	Carousel.prototype.maxIndex = function () {
		return Math.max( 0, this.slides.length - this.perView() );
	};

	Carousel.prototype.layout = function () {
		var pv = this.perView();
		var basis = ( 100 / pv ) + '%';

		this.slides.forEach( function ( slide ) {
			slide.style.flex     = '0 0 ' + basis;
			slide.style.maxWidth = basis;
		} );

		if ( this.index > this.maxIndex() ) {
			this.index = this.maxIndex();
		}

		this.buildDots();
		this.update();
	};

	Carousel.prototype.update = function () {
		this.track.style.transform = 'translateX(-' + ( this.index * ( 100 / this.perView() ) ) + '%)';

		if ( this.dotsWrap ) {
			Array.prototype.forEach.call( this.dotsWrap.children, function ( dot, i ) {
				var active = i === this.index;
				dot.classList.toggle( 'is-active', active );
				if ( active ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			}.bind( this ) );
		}
	};

	Carousel.prototype.goTo = function ( index ) {
		var max = this.maxIndex();
		if ( index > max ) {
			index = 0;
		} else if ( index < 0 ) {
			index = max;
		}
		this.index = index;
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
		var total = this.maxIndex() + 1;

		if ( total <= 1 ) {
			return;
		}

		for ( var i = 0; i < total; i++ ) {
			var dot = document.createElement( 'button' );
			dot.type = 'button';
			dot.className = 'at-carousel__dot';
			dot.setAttribute( 'aria-label', 'Go to slide ' + ( i + 1 ) );
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
		if ( ! this.autoplay || reduceMotion || this.maxIndex() < 1 ) {
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

	/**
	 * Continuous marquee. The item set is rendered twice in the markup, so
	 * animating the track by -50% loops seamlessly. Duration is derived from
	 * the track width to keep a constant pixels-per-second speed regardless of
	 * how many testimonials are shown.
	 *
	 * @param {Element} root The [data-at-marquee] element.
	 */
	function Marquee( root ) {
		var track = root.querySelector( '.at-marquee__track' );
		if ( ! track ) {
			return;
		}

		var speed = parseFloat( root.getAttribute( 'data-speed' ) ) || 45; // Pixels per second.

		function apply() {
			var half     = track.scrollWidth / 2; // One of the two identical halves.
			var viewport = root.querySelector( '.at-marquee__viewport' );
			var visible  = viewport ? viewport.clientWidth : root.clientWidth;

			// Content does not overflow: nothing to scroll, just center it.
			if ( half <= visible ) {
				root.classList.add( 'at-marquee--static' );
				return;
			}

			root.classList.remove( 'at-marquee--static' );
			track.style.setProperty( '--at-marquee-duration', ( half / speed ).toFixed( 2 ) + 's' );
		}

		apply();

		var resizeTimer;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( apply, 150 );
		} );
	}

	/**
	 * Group filter tabs. Shows/hides the already-rendered cards by their
	 * data-at-groups slugs. Works for grid, list, card and masonry layouts.
	 *
	 * @param {Element} bar The .at-filter element.
	 */
	function Filter( bar ) {
		var wrap = bar.closest( '.at-wrapper' );
		if ( ! wrap ) {
			return;
		}

		var container = wrap.querySelector( '.at-grid, .at-list, .at-card-layout, .at-masonry' );
		if ( ! container ) {
			return;
		}

		var cards = Array.prototype.slice.call( container.querySelectorAll( '.at-card' ) );
		var btns  = Array.prototype.slice.call( bar.querySelectorAll( '.at-filter__btn' ) );

		bar.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest( '.at-filter__btn' );
			if ( ! btn ) {
				return;
			}

			var want = btn.getAttribute( 'data-at-filter' );

			btns.forEach( function ( b ) {
				var active = b === btn;
				b.classList.toggle( 'is-active', active );
				b.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			} );

			cards.forEach( function ( card ) {
				var host   = card.closest( '.at-masonry__item' ) || card;
				var groups = ( card.getAttribute( 'data-at-groups' ) || '' ).split( ' ' );
				var show   = '*' === want || groups.indexOf( want ) !== -1;
				host.style.display = show ? '' : 'none';
			} );
		} );
	}

	/**
	 * Read more toggles. Clamps long reviews and reveals a toggle only when the
	 * text actually overflows. Gated by the .at-has-readmore class so visitors
	 * without JS keep the full review text.
	 */
	function initReadMore() {
		var buttons = document.querySelectorAll( '.at-readmore' );
		Array.prototype.forEach.call( buttons, function ( btn ) {
			if ( btn.getAttribute( 'data-at-ready' ) ) {
				return;
			}
			btn.setAttribute( 'data-at-ready', '1' );

			var wrap = btn.closest( '.at-wrapper' );
			if ( wrap ) {
				wrap.classList.add( 'at-has-readmore' );
			}

			var review = btn.previousElementSibling;
			if ( ! review || review.className.indexOf( 'at-card__review' ) === -1 ) {
				btn.style.display = 'none';
				return;
			}

			// Fits within the clamp already: no toggle needed.
			if ( review.scrollHeight - review.clientHeight <= 2 ) {
				review.classList.remove( 'at-card__review--clamp' );
				if ( btn.parentNode ) {
					btn.parentNode.removeChild( btn );
				}
				return;
			}

			btn.addEventListener( 'click', function () {
				var expanded = review.classList.toggle( 'is-expanded' );
				btn.textContent = expanded ? btn.getAttribute( 'data-less' ) : btn.getAttribute( 'data-more' );
				btn.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			} );
		} );
	}

	/**
	 * Load more. Reveals hidden cards in batches. Gated by .at-has-loadmore so
	 * visitors without JS see every card and no button.
	 */
	function initLoadMore() {
		var buttons = document.querySelectorAll( '.at-loadmore' );
		Array.prototype.forEach.call( buttons, function ( btn ) {
			if ( btn.getAttribute( 'data-at-ready' ) ) {
				return;
			}
			btn.setAttribute( 'data-at-ready', '1' );

			var wrap = btn.closest( '.at-wrapper' );
			if ( ! wrap ) {
				return;
			}
			wrap.classList.add( 'at-has-loadmore' );

			var container = wrap.querySelector( '.at-grid, .at-list, .at-card-layout, .at-masonry' );
			if ( ! container ) {
				return;
			}

			var batch = parseInt( btn.getAttribute( 'data-batch' ), 10 ) || 6;

			btn.addEventListener( 'click', function () {
				var hidden = container.querySelectorAll( '.at-lm-collapsed' );
				var count  = Math.min( batch, hidden.length );
				for ( var i = 0; i < count; i++ ) {
					hidden[ i ].classList.remove( 'at-lm-collapsed' );
				}
				if ( container.querySelectorAll( '.at-lm-collapsed' ).length === 0 ) {
					var box = btn.parentNode;
					if ( box ) {
						box.style.display = 'none';
					}
				}
			} );
		} );
	}

	function init() {
		initReadMore();
		initLoadMore();

		var filters = document.querySelectorAll( '.at-filter' );
		Array.prototype.forEach.call( filters, function ( bar ) {
			if ( ! bar.getAttribute( 'data-at-ready' ) ) {
				bar.setAttribute( 'data-at-ready', '1' );
				new Filter( bar );
			}
		} );

		var carousels = document.querySelectorAll( '[data-at-carousel]' );
		Array.prototype.forEach.call( carousels, function ( root ) {
			if ( ! root.getAttribute( 'data-at-ready' ) ) {
				root.setAttribute( 'data-at-ready', '1' );
				new Carousel( root );
			}
		} );

		var marquees = document.querySelectorAll( '[data-at-marquee]' );
		Array.prototype.forEach.call( marquees, function ( root ) {
			if ( ! root.getAttribute( 'data-at-ready' ) ) {
				root.setAttribute( 'data-at-ready', '1' );
				new Marquee( root );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
