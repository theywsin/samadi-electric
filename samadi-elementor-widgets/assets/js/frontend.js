/**
 * Samadi Elementor Widgets — front-end behaviour. Vanilla JS, progressive enhancement:
 * every feature works without JavaScript (plain links and GET forms) and is enhanced here.
 */
( function () {
	'use strict';

	var cfg = window.samadiEW || { rest: '/wp-json/samadi/v1/', i18n: {} };
	var i18n = cfg.i18n || {};
	var faDigits = !! ( window.samadiTheme && window.samadiTheme.persianDigits );
	var isRtl = 'rtl' === document.documentElement.dir || document.body.classList.contains( 'rtl' );

	document.documentElement.classList.add( 'se-js' );

	function toFa( value ) {
		var s = String( value );
		return faDigits ? s.replace( /\d/g, function ( d ) {
			return '۰۱۲۳۴۵۶۷۸۹'.charAt( +d );
		} ) : s;
	}

	function announce( message ) {
		if ( window.samadiAnnounce ) {
			window.samadiAnnounce( message );
			return;
		}
		var region = document.getElementById( 'se-live' );
		if ( region ) {
			region.textContent = message;
		}
	}

	function debounce( fn, wait ) {
		var t;
		return function () {
			var args = arguments;
			var ctx = this;
			window.clearTimeout( t );
			t = window.setTimeout( function () {
				fn.apply( ctx, args );
			}, wait );
		};
	}

	function el( tag, className, text ) {
		var node = document.createElement( tag );
		if ( className ) {
			node.className = className;
		}
		if ( undefined !== text ) {
			node.textContent = text;
		}
		return node;
	}

	/* =====================================================================
	 * Live search (combobox + listbox pattern)
	 * =================================================================== */

	var liveCount = 0;

	function initLiveSearch( root ) {
		if ( root.__seLive || ! cfg.liveSearch ) {
			return;
		}
		root.__seLive = true;

		var input = root.querySelector( 'input[name="s"]' );
		if ( ! input ) {
			return;
		}

		var id = 'se-live-' + ( ++liveCount );
		var panel = el( 'div', 'se-live' );
		panel.id = id;
		panel.hidden = true;
		root.appendChild( panel );

		input.setAttribute( 'role', 'combobox' );
		input.setAttribute( 'aria-autocomplete', 'list' );
		input.setAttribute( 'aria-expanded', 'false' );
		input.setAttribute( 'aria-controls', id );

		var controller = null;
		var options = [];
		var active = -1;

		function close() {
			panel.hidden = true;
			input.setAttribute( 'aria-expanded', 'false' );
			input.removeAttribute( 'aria-activedescendant' );
			active = -1;
		}

		function setActive( index ) {
			options.forEach( function ( opt, i ) {
				opt.setAttribute( 'aria-selected', i === index ? 'true' : 'false' );
			} );
			active = index;
			if ( options[ index ] ) {
				input.setAttribute( 'aria-activedescendant', options[ index ].id );
				options[ index ].scrollIntoView( { block: 'nearest' } );
			} else {
				input.removeAttribute( 'aria-activedescendant' );
			}
		}

		function group( label, items, build ) {
			if ( ! items || ! items.length ) {
				return;
			}
			panel.appendChild( el( 'p', 'se-live__label', label ) );
			var list = el( 'ul', 'se-live__group' );
			list.setAttribute( 'role', 'listbox' );
			list.setAttribute( 'aria-label', label );
			items.forEach( function ( item ) {
				var li = el( 'li', 'se-live__item' );
				li.setAttribute( 'role', 'option' );
				li.id = id + '-opt-' + options.length;
				li.setAttribute( 'aria-selected', 'false' );
				var a = el( 'a' );
				a.href = item.url;
				a.tabIndex = -1;
				build( a, item );
				li.appendChild( a );
				list.appendChild( li );
				options.push( li );
			} );
			panel.appendChild( list );
		}

		function render( data ) {
			panel.innerHTML = '';
			options = [];
			active = -1;

			var hasAny = ( data.products && data.products.length ) || ( data.categories && data.categories.length ) || ( data.brands && data.brands.length );

			if ( ! hasAny ) {
				panel.appendChild( el( 'p', 'se-live__empty', i18n.noResults ) );
			}

			group( i18n.products, data.products, function ( a, p ) {
				var img = el( 'img', 'se-live__thumb' );
				img.src = p.image;
				img.alt = '';
				img.width = 48;
				img.height = 48;
				img.loading = 'lazy';
				var body = el( 'span', 'se-live__body' );
				body.appendChild( el( 'span', 'se-live__title', p.title ) );
				var meta = el( 'span', 'se-live__meta' );
				// price is server-side sanitized WooCommerce price HTML.
				meta.innerHTML = p.price || '';
				if ( p.sku ) {
					var sku = el( 'span', '', ' · ' + i18n.sku + ' ' + p.sku );
					sku.dir = 'auto';
					meta.appendChild( sku );
				}
				body.appendChild( meta );
				a.appendChild( img );
				a.appendChild( body );
			} );

			group( i18n.categories, data.categories, function ( a, c ) {
				a.appendChild( el( 'span', 'se-live__title', c.name ) );
			} );

			group( i18n.brands, data.brands, function ( a, b ) {
				a.appendChild( el( 'span', 'se-live__title', b.name ) );
			} );

			if ( hasAny && data.all ) {
				var all = el( 'a', 'se-live__all', i18n.viewAll );
				all.href = data.all;
				panel.appendChild( all );
			}

			panel.hidden = false;
			input.setAttribute( 'aria-expanded', 'true' );
		}

		var search = debounce( function () {
			var term = input.value.trim();
			if ( term.length < 2 ) {
				close();
				return;
			}
			if ( controller ) {
				controller.abort();
			}
			controller = 'AbortController' in window ? new AbortController() : null;

			fetch( cfg.rest + 'search?q=' + encodeURIComponent( term ), {
				credentials: 'same-origin',
				signal: controller ? controller.signal : undefined,
			} )
				.then( function ( r ) {
					return r.ok ? r.json() : Promise.reject( r );
				} )
				.then( function ( data ) {
					if ( input.value.trim() === term ) {
						render( data );
					}
				} )
				.catch( function () {} );
		}, 250 );

		input.addEventListener( 'input', search );

		input.addEventListener( 'keydown', function ( event ) {
			if ( panel.hidden ) {
				return;
			}
			if ( 'ArrowDown' === event.key ) {
				event.preventDefault();
				setActive( Math.min( options.length - 1, active + 1 ) );
			} else if ( 'ArrowUp' === event.key ) {
				event.preventDefault();
				setActive( Math.max( -1, active - 1 ) );
			} else if ( 'Enter' === event.key && options[ active ] ) {
				event.preventDefault();
				window.location.href = options[ active ].querySelector( 'a' ).href;
			} else if ( 'Escape' === event.key ) {
				close();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! root.contains( event.target ) ) {
				close();
			}
		} );

		root.addEventListener( 'focusout', function ( event ) {
			if ( event.relatedTarget && ! root.contains( event.relatedTarget ) ) {
				close();
			}
		} );
	}

	/* =====================================================================
	 * Product filters (AJAX + history, falls back to normal navigation)
	 * =================================================================== */

	var RESULTS = '[data-se-shop-results], .elementor-widget-wc-archive-products';

	function resultsContainer() {
		return document.querySelector( RESULTS );
	}

	function filterUrl( form ) {
		var data = new FormData( form );
		var grouped = {};
		var order = [];

		data.forEach( function ( value, rawName ) {
			var name = rawName.replace( /\[\]$/, '' );
			value = String( value ).trim();
			if ( '' === value ) {
				return;
			}
			if ( ! grouped[ name ] ) {
				grouped[ name ] = [];
				order.push( name );
			}
			if ( grouped[ name ].indexOf( value ) === -1 ) {
				grouped[ name ].push( value );
			}
		} );

		var params = new URLSearchParams();
		order.forEach( function ( name ) {
			if ( 0 === name.indexOf( 'query_type_' ) && ! grouped[ 'filter_' + name.slice( 11 ) ] ) {
				return;
			}
			params.set( name, grouped[ name ].join( ',' ) );
		} );

		var query = params.toString().replace( /%2C/gi, ',' );
		return form.action.split( '?' )[ 0 ] + ( query ? '?' + query : '' );
	}

	function restoreFocus( name, value ) {
		if ( ! name ) {
			return;
		}
		var selector = '[data-se-filter-form] [name="' + name.replace( /"/g, '' ) + '"]' + ( value ? '[value="' + String( value ).replace( /"/g, '' ) + '"]' : '' );
		var target = document.querySelector( selector );
		if ( target ) {
			target.focus( { preventScroll: true } );
		}
	}

	var loading = null;

	function loadResults( url, push, focusInfo ) {
		var container = resultsContainer();
		if ( ! container || ! window.fetch || ! window.DOMParser ) {
			window.location.assign( url );
			return;
		}

		if ( loading ) {
			loading.abort();
		}
		loading = 'AbortController' in window ? new AbortController() : null;

		container.classList.add( 'is-loading' );
		container.setAttribute( 'aria-busy', 'true' );

		fetch( url, {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			signal: loading ? loading.signal : undefined,
		} )
			.then( function ( r ) {
				return r.ok || 404 === r.status ? r.text() : Promise.reject( r );
			} )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );
				var fresh = doc.querySelector( RESULTS );
				if ( ! fresh ) {
					window.location.assign( url );
					return;
				}

				container.innerHTML = fresh.innerHTML;
				container.classList.remove( 'is-loading' );
				container.removeAttribute( 'aria-busy' );

				var oldForms = document.querySelectorAll( '[data-se-filter-form]' );
				var newForms = doc.querySelectorAll( '[data-se-filter-form]' );
				Array.prototype.forEach.call( oldForms, function ( form, i ) {
					if ( newForms[ i ] ) {
						form.replaceWith( document.importNode( newForms[ i ], true ) );
					}
				} );

				if ( doc.title ) {
					document.title = doc.title;
				}
				if ( push ) {
					window.history.pushState( { seFilters: true }, '', url );
				}

				var count = container.querySelector( '.woocommerce-result-count' );
				announce( count ? count.textContent.trim() : ( i18n.results || '' ) );

				var top = container.getBoundingClientRect().top;
				if ( top < 0 ) {
					container.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}

				if ( focusInfo ) {
					restoreFocus( focusInfo.name, focusInfo.value );
				}

				initAll( container );
				document.dispatchEvent( new CustomEvent( 'samadi:filters-applied' ) );
				document.dispatchEvent( new CustomEvent( 'samadi:results-updated' ) );
				if ( window.jQuery ) {
					window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
				}
			} )
			.catch( function ( err ) {
				if ( err && 'AbortError' === err.name ) {
					return;
				}
				announce( i18n.loadError );
				window.location.assign( url );
			} );
	}

	var desktop = window.matchMedia( '(min-width: 992px)' );

	document.addEventListener( 'change', function ( event ) {
		var form = event.target.closest && event.target.closest( '[data-se-filter-form]' );
		if ( ! form ) {
			return;
		}
		// Desktop applies instantly; mobile waits for "Apply" inside the panel.
		if ( desktop.matches && ( 'checkbox' === event.target.type || 'number' === event.target.type ) ) {
			loadResults( filterUrl( form ), true, { name: event.target.name, value: 'checkbox' === event.target.type ? event.target.value : '' } );
		}
	} );

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest && event.target.closest( '[data-se-filter-form]' );
		if ( ! form ) {
			return;
		}
		event.preventDefault();
		loadResults( filterUrl( form ), true );
	} );

	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest && event.target.closest( '[data-se-filter-link], ' + '[data-se-shop-results] .woocommerce-pagination a' );
		if ( link && link.href && ! event.ctrlKey && ! event.metaKey && ! event.shiftKey ) {
			event.preventDefault();
			loadResults( link.href, true );
			return;
		}

		var more = event.target.closest && event.target.closest( '[data-se-filter-more]' );
		if ( more ) {
			var options = more.previousElementSibling;
			var expanded = options.classList.toggle( 'is-expanded' );
			more.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
		}
	} );

	// Sorting inside AJAX-capable results: intercept before WooCommerce's submit handler.
	document.addEventListener( 'change', function ( event ) {
		var select = event.target;
		if ( ! select.matches || ! select.matches( '[data-se-shop-results] .woocommerce-ordering select.orderby' ) ) {
			return;
		}
		event.stopImmediatePropagation();
		var form = select.form;
		var params = new URLSearchParams( new FormData( form ) );
		params.delete( 'paged' );
		var path = window.location.pathname.replace( /\/page\/\d+\/?$/, '/' );
		loadResults( path + '?' + params.toString().replace( /%2C/gi, ',' ), true );
	}, true );

	window.addEventListener( 'popstate', function () {
		if ( resultsContainer() && document.querySelector( '[data-se-filter-form], [data-se-shop-results]' ) ) {
			loadResults( window.location.href, false );
		}
	} );

	/* =====================================================================
	 * Carousels
	 * =================================================================== */

	function initCarousel( root ) {
		if ( root.__seCarousel ) {
			root.__seCarouselUpdate && root.__seCarouselUpdate();
			return;
		}
		var track = root.querySelector( '.se-carousel__track ul.products, .se-carousel__track .se-cat-grid, .se-carousel__track .se-brand-strip' );
		var prev = root.querySelector( '[data-dir="prev"]' );
		var next = root.querySelector( '[data-dir="next"]' );
		if ( ! track || ! prev || ! next ) {
			return;
		}
		root.__seCarousel = true;

		var sign = isRtl ? -1 : 1;

		function update() {
			var max = track.scrollWidth - track.clientWidth;
			var pos = Math.abs( track.scrollLeft );
			root.classList.toggle( 'is-scrollable', max > 4 );
			prev.disabled = pos <= 2;
			next.disabled = pos >= max - 2;
		}

		function go( dir ) {
			track.scrollBy( { left: sign * dir * track.clientWidth * 0.9, behavior: 'smooth' } );
		}

		prev.addEventListener( 'click', function () {
			go( -1 );
		} );
		next.addEventListener( 'click', function () {
			go( 1 );
		} );
		track.addEventListener( 'scroll', debounce( update, 60 ), { passive: true } );
		window.addEventListener( 'resize', debounce( update, 150 ) );
		root.__seCarouselUpdate = update;
		update();
	}

	/* =====================================================================
	 * Hero slider
	 * =================================================================== */

	function initSlider( root ) {
		if ( root.__seSlider || ! root.classList.contains( 'is-multi' ) ) {
			return;
		}
		root.__seSlider = true;

		var track = root.querySelector( '.se-hero-slider__track' );
		var slides = Array.prototype.slice.call( track.children );
		var dots = Array.prototype.slice.call( root.querySelectorAll( '.se-hero-slider__dot' ) );
		var current = 0;
		var timer = null;
		var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		function show( index ) {
			current = ( index + slides.length ) % slides.length;
			track.scrollTo( { left: ( isRtl ? -1 : 1 ) * current * track.clientWidth, behavior: reduce ? 'auto' : 'smooth' } );
		}

		function mark( index ) {
			current = index;
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'is-active', i === index );
				if ( i === index ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			} );
		}

		if ( 'IntersectionObserver' in window ) {
			var io = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						mark( slides.indexOf( entry.target ) );
					}
				} );
			}, { root: track, threshold: 0.6 } );
			slides.forEach( function ( s ) {
				io.observe( s );
			} );
		}

		dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				show( i );
			} );
		} );

		function stop() {
			window.clearInterval( timer );
			timer = null;
		}

		function start() {
			if ( reduce || '1' !== root.getAttribute( 'data-autoplay' ) || timer ) {
				return;
			}
			timer = window.setInterval( function () {
				if ( ! document.hidden ) {
					show( current + 1 );
				}
			}, ( parseInt( root.getAttribute( 'data-interval' ), 10 ) || 6 ) * 1000 );
		}

		root.addEventListener( 'mouseenter', stop );
		root.addEventListener( 'mouseleave', start );
		root.addEventListener( 'focusin', stop );
		root.addEventListener( 'touchstart', stop, { passive: true } );
		start();
	}

	/* =====================================================================
	 * Countdown
	 * =================================================================== */

	function initCountdown( root ) {
		if ( root.__seCountdown ) {
			return;
		}
		root.__seCountdown = true;
		var end = Date.parse( root.getAttribute( 'data-se-countdown' ) );
		if ( isNaN( end ) ) {
			return;
		}
		var units = {
			d: root.querySelector( '[data-unit="d"] b' ),
			h: root.querySelector( '[data-unit="h"] b' ),
			m: root.querySelector( '[data-unit="m"] b' ),
			s: root.querySelector( '[data-unit="s"] b' ),
		};

		function pad( n ) {
			return toFa( n < 10 ? '0' + n : String( n ) );
		}

		function tick() {
			var left = Math.max( 0, Math.floor( ( end - Date.now() ) / 1000 ) );
			units.d.textContent = toFa( Math.floor( left / 86400 ) );
			units.h.textContent = pad( Math.floor( ( left % 86400 ) / 3600 ) );
			units.m.textContent = pad( Math.floor( ( left % 3600 ) / 60 ) );
			units.s.textContent = pad( left % 60 );
			if ( ! left ) {
				window.clearInterval( interval );
			}
		}

		var interval = window.setInterval( tick, 1000 );
		tick();
	}

	/* =====================================================================
	 * Recently viewed
	 * =================================================================== */

	var RV_KEY = 'samadiRecentlyViewed';

	function storedIds() {
		try {
			var ids = JSON.parse( window.localStorage.getItem( RV_KEY ) || '[]' );
			return Array.isArray( ids ) ? ids.filter( function ( id ) {
				return Number.isInteger( id ) && id > 0;
			} ) : [];
		} catch ( e ) {
			return [];
		}
	}

	function trackView() {
		var id = parseInt( cfg.productId, 10 );
		if ( ! id ) {
			return;
		}
		var ids = storedIds().filter( function ( x ) {
			return x !== id;
		} );
		ids.unshift( id );
		try {
			window.localStorage.setItem( RV_KEY, JSON.stringify( ids.slice( 0, 20 ) ) );
		} catch ( e ) {}
	}

	function initRecentlyViewed( root ) {
		if ( root.__seRv ) {
			return;
		}
		root.__seRv = true;
		var current = parseInt( cfg.productId, 10 );
		var limit = parseInt( root.getAttribute( 'data-limit' ), 10 ) || 8;
		var ids = storedIds().filter( function ( x ) {
			return x !== current;
		} ).slice( 0, limit );

		if ( ! ids.length ) {
			return;
		}

		fetch( cfg.rest + 'products?ids=' + ids.join( ',' ) + '&columns=' + ( parseInt( root.getAttribute( 'data-columns' ), 10 ) || 4 ), { credentials: 'same-origin' } )
			.then( function ( r ) {
				return r.ok ? r.json() : Promise.reject( r );
			} )
			.then( function ( data ) {
				if ( ! data || ! data.html ) {
					return;
				}
				var list = root.querySelector( '[data-se-recently-viewed-list]' );
				// Server-rendered WooCommerce product cards.
				list.innerHTML = data.html;
				root.hidden = false;
				root.querySelectorAll( '[data-se-carousel]' ).forEach( initCarousel );
			} )
			.catch( function () {} );
	}

	/* =====================================================================
	 * Quote form rows
	 * =================================================================== */

	function renumber( rows ) {
		Array.prototype.forEach.call( rows.children, function ( row, i ) {
			row.querySelectorAll( 'input, label' ).forEach( function ( node ) {
				[ 'name', 'id', 'for' ].forEach( function ( attr ) {
					var v = node.getAttribute( attr );
					if ( v ) {
						node.setAttribute( attr, v.replace( /\[\d+\]/, '[' + i + ']' ).replace( /-(\d+)$/, '-' + i ) );
					}
				} );
			} );
		} );
	}

	function addRemoveButton( row, rows ) {
		if ( row.querySelector( '.se-quote__row-remove' ) ) {
			return;
		}
		var btn = el( 'button', 'se-quote__row-remove' );
		btn.type = 'button';
		btn.setAttribute( 'aria-label', i18n.removeRow || '×' );
		btn.textContent = '×';
		btn.addEventListener( 'click', function () {
			if ( rows.children.length > 1 ) {
				row.remove();
				renumber( rows );
				var last = rows.lastElementChild && rows.lastElementChild.querySelector( 'input' );
				if ( last ) {
					last.focus();
				}
			}
		} );
		row.appendChild( btn );
	}

	function initQuote( rows ) {
		if ( rows.__seQuote ) {
			return;
		}
		rows.__seQuote = true;
		var max = parseInt( rows.getAttribute( 'data-max' ), 10 ) || 30;
		var add = rows.parentNode.querySelector( '[data-se-quote-add]' );

		Array.prototype.forEach.call( rows.children, function ( row ) {
			addRemoveButton( row, rows );
		} );

		if ( add ) {
			add.addEventListener( 'click', function () {
				if ( rows.children.length >= max ) {
					return;
				}
				var clone = rows.lastElementChild.cloneNode( true );
				clone.querySelectorAll( 'input' ).forEach( function ( input ) {
					input.value = '';
				} );
				var old = clone.querySelector( '.se-quote__row-remove' );
				if ( old ) {
					old.remove();
				}
				rows.appendChild( clone );
				renumber( rows );
				addRemoveButton( clone, rows );
				clone.querySelector( 'input' ).focus();
			} );
		}
	}

	/* =====================================================================
	 * Mega menu (widgets placed with Elementor; the theme handles its own header menu)
	 * =================================================================== */

	function initMega( root ) {
		if ( root.hasAttribute( 'data-se-mega-ready' ) ) {
			return;
		}
		root.setAttribute( 'data-se-mega-ready', '' );
		var toggle = root.querySelector( '.se-mega__toggle' );
		var panel = root.querySelector( '.se-mega__panel' );
		if ( ! toggle || ! panel ) {
			return;
		}
		function set( open ) {
			panel.hidden = ! open;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}
		toggle.addEventListener( 'click', function () {
			set( panel.hidden );
		} );
		document.addEventListener( 'click', function ( event ) {
			if ( ! panel.hidden && ! root.contains( event.target ) ) {
				set( false );
			}
		} );
		root.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! panel.hidden ) {
				set( false );
				toggle.focus();
			}
		} );
	}

	/* =====================================================================
	 * Init
	 * =================================================================== */

	function initAll( scope ) {
		scope = scope || document;
		scope.querySelectorAll( '[data-se-live-search]' ).forEach( initLiveSearch );
		scope.querySelectorAll( '[data-se-carousel]' ).forEach( initCarousel );
		scope.querySelectorAll( '[data-se-slider]' ).forEach( initSlider );
		scope.querySelectorAll( '[data-se-countdown]' ).forEach( initCountdown );
		scope.querySelectorAll( '[data-se-recently-viewed]' ).forEach( initRecentlyViewed );
		scope.querySelectorAll( '[data-se-quote-rows]' ).forEach( initQuote );
		scope.querySelectorAll( '[data-se-mega]' ).forEach( initMega );
	}

	function boot() {
		trackView();
		initAll( document );

		// Quote form result: move focus to the message/errors.
		if ( /[?&]se_quote=/.test( window.location.search ) ) {
			var box = document.getElementById( 'se-quote-form' );
			if ( box ) {
				box.focus();
			}
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Elementor editor/preview: re-init widgets when they are (re)rendered (Elementor uses jQuery events).
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
					initAll( $scope[ 0 ] );
				} );
			}
		} );
	}
}() );
