/**
 * Samadi Electric — shop UI: quantity steppers, variation swatches, sticky buy bar,
 * grid/list toggle and the mobile filter panel. Business logic stays in WooCommerce.
 */
( function () {
	'use strict';

	var i18n = ( window.samadiTheme && window.samadiTheme.i18n ) || {};

	function fire( el, type ) {
		el.dispatchEvent( new Event( type, { bubbles: true } ) );
	}

	/* ---------- Quantity steppers ---------- */

	function decimals( step ) {
		var s = String( step );
		return s.indexOf( '.' ) > -1 ? s.split( '.' )[ 1 ].length : 0;
	}

	function enhanceQuantity( wrap ) {
		if ( wrap.classList.contains( 'se-qty' ) ) {
			return;
		}
		var input = wrap.querySelector( 'input.qty' );
		if ( ! input || 'hidden' === input.type || input.readOnly ) {
			return;
		}
		wrap.classList.add( 'se-qty' );

		var make = function ( dir ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'se-qty__btn se-qty__btn--' + ( dir > 0 ? 'plus' : 'minus' );
			btn.setAttribute( 'aria-label', dir > 0 ? ( i18n.increase || '+' ) : ( i18n.decrease || '-' ) );
			btn.innerHTML = dir > 0
				? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>'
				: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>';
			btn.addEventListener( 'click', function () {
				var step = parseFloat( input.step ) || 1;
				var min = '' !== input.min ? parseFloat( input.min ) : 0;
				var max = '' !== input.max ? parseFloat( input.max ) : Infinity;
				var value = parseFloat( input.value ) || 0;
				var next = value + dir * step;
				if ( next < min ) {
					next = min;
				}
				if ( next > max ) {
					next = max;
				}
				next = parseFloat( next.toFixed( decimals( step ) ) );
				if ( next !== value ) {
					input.value = next;
					fire( input, 'input' );
					fire( input, 'change' );
				}
			} );
			return btn;
		};

		// RTL: "+" sits at the start (right), "-" at the end (left).
		input.insertAdjacentElement( 'beforebegin', make( 1 ) );
		input.insertAdjacentElement( 'afterend', make( -1 ) );
	}

	function enhanceQuantities( root ) {
		( root || document ).querySelectorAll( '.quantity' ).forEach( enhanceQuantity );
	}

	/* ---------- Variation swatches ---------- */

	function buildSwatches( select ) {
		if ( select.classList.contains( 'se-has-swatches' ) ) {
			return;
		}
		var colors = {};
		try {
			colors = JSON.parse( select.getAttribute( 'data-se-colors' ) || '{}' ) || {};
		} catch ( e ) {
			colors = {};
		}

		var labelEl = select.id ? document.querySelector( 'label[for="' + select.id + '"]' ) : null;
		var group = document.createElement( 'div' );
		group.className = 'se-swatches';
		group.setAttribute( 'role', 'radiogroup' );
		if ( labelEl ) {
			if ( ! labelEl.id ) {
				labelEl.id = select.id + '-label';
			}
			group.setAttribute( 'aria-labelledby', labelEl.id );
		}

		select.classList.add( 'se-has-swatches' );
		select.setAttribute( 'tabindex', '-1' );
		select.setAttribute( 'aria-hidden', 'true' );
		select.parentNode.insertBefore( group, select );

		var render = function () {
			group.innerHTML = '';
			Array.prototype.forEach.call( select.options, function ( option ) {
				if ( ! option.value ) {
					return;
				}
				var btn = document.createElement( 'button' );
				var color = colors[ option.value ];
				btn.type = 'button';
				btn.className = 'se-swatch' + ( color ? ' se-swatch--color' : '' );
				btn.setAttribute( 'role', 'radio' );
				btn.setAttribute( 'data-value', option.value );
				btn.setAttribute( 'aria-checked', select.value === option.value ? 'true' : 'false' );
				btn.setAttribute( 'tabindex', select.value === option.value || ( ! select.value && option === select.options[ 1 ] ) ? '0' : '-1' );
				if ( option.disabled ) {
					btn.setAttribute( 'aria-disabled', 'true' );
				}
				if ( color ) {
					btn.setAttribute( 'aria-label', option.text );
					btn.title = option.text;
					btn.innerHTML = '<span class="se-swatch__color" aria-hidden="true"></span>';
					btn.firstChild.style.setProperty( '--se-swatch', color );
				} else {
					btn.textContent = option.text;
				}
				group.appendChild( btn );
			} );
		};

		group.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest( '.se-swatch' );
			if ( ! btn || 'true' === btn.getAttribute( 'aria-disabled' ) ) {
				return;
			}
			var value = btn.getAttribute( 'data-value' );
			select.value = select.value === value ? '' : value;
			fire( select, 'change' );
			render();
			var focused = group.querySelector( '[data-value="' + value + '"]' );
			if ( focused ) {
				focused.focus();
			}
		} );

		group.addEventListener( 'keydown', function ( event ) {
			var keys = { ArrowLeft: 1, ArrowDown: 1, ArrowRight: -1, ArrowUp: -1 };
			if ( ! ( event.key in keys ) ) {
				return;
			}
			event.preventDefault();
			var items = Array.prototype.slice.call( group.querySelectorAll( '.se-swatch:not([aria-disabled="true"])' ) );
			var index = items.indexOf( document.activeElement );
			var next = items[ ( index + keys[ event.key ] + items.length ) % items.length ];
			if ( next ) {
				next.click();
			}
		} );

		select.addEventListener( 'change', render );
		// WooCommerce triggers jQuery-only change events (e.g. "Clear" link).
		if ( window.jQuery ) {
			window.jQuery( select ).on( 'change', render );
		}

		// WooCommerce rebuilds <option>s when availability changes.
		new MutationObserver( render ).observe( select, { childList: true, subtree: true, attributes: true } );

		render();
	}

	function initSwatches( root ) {
		( root || document ).querySelectorAll( 'form.variations_form table.variations select' ).forEach( buildSwatches );
	}

	/* ---------- Sticky buy bar (mobile) ---------- */

	function initBuyBar() {
		var bar = document.querySelector( '[data-se-buybar]' );
		var form = document.querySelector( '.se-single-product form.cart' );
		if ( ! bar || ! form || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		new IntersectionObserver( function ( entries ) {
			var visible = ! entries[ 0 ].isIntersecting && entries[ 0 ].boundingClientRect.top < 0;
			bar.hidden = ! visible;
			document.body.classList.toggle( 'se-buybar-visible', visible );
		} ).observe( form );

		bar.querySelector( '[data-se-buybar-btn]' ).addEventListener( 'click', function () {
			var button = form.querySelector( '.single_add_to_cart_button' );
			var needsChoice = button && ( button.classList.contains( 'disabled' ) || button.disabled );

			form.scrollIntoView( { behavior: 'smooth', block: 'center' } );

			if ( needsChoice ) {
				var first = form.querySelector( '.se-swatch, select' );
				if ( first ) {
					first.focus( { preventScroll: true } );
				}
				return;
			}
			if ( button ) {
				button.click();
			}
		} );
	}

	/* ---------- Grid / list toggle ---------- */

	var VIEW_KEY = 'samadiShopView';

	function applyView( view ) {
		var results = document.querySelector( '[data-se-shop-results]' );
		if ( ! results ) {
			return;
		}
		results.classList.toggle( 'se-view-list', 'list' === view );
		results.querySelectorAll( '[data-se-view]' ).forEach( function ( btn ) {
			var active = btn.getAttribute( 'data-se-view' ) === view;
			btn.classList.toggle( 'is-active', active );
			btn.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		} );
	}

	function storedView() {
		try {
			return window.localStorage.getItem( VIEW_KEY ) || 'grid';
		} catch ( e ) {
			return 'grid';
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest( '[data-se-view]' );
		if ( ! btn ) {
			return;
		}
		var view = btn.getAttribute( 'data-se-view' );
		try {
			window.localStorage.setItem( VIEW_KEY, view );
		} catch ( e ) {}
		applyView( view );
	} );

	/* ---------- Mobile filter panel ---------- */

	function initFilters() {
		var panel = document.querySelector( '[data-se-filters]' );
		if ( ! panel || ! window.SamadiDialog ) {
			return;
		}
		var isMobile = window.matchMedia( '(max-width: 991px)' );

		var close = function () {
			if ( ! panel.classList.contains( 'is-open' ) ) {
				return;
			}
			panel.classList.remove( 'is-open' );
			panel.removeAttribute( 'role' );
			panel.removeAttribute( 'aria-modal' );
			window.SamadiDialog.close( panel );
		};

		document.addEventListener( 'click', function ( event ) {
			var opener = event.target.closest( '[data-se-filters-open]' );
			if ( opener && isMobile.matches ) {
				panel.classList.add( 'is-open' );
				panel.setAttribute( 'role', 'dialog' );
				panel.setAttribute( 'aria-modal', 'true' );
				panel.setAttribute( 'tabindex', '-1' );
				window.SamadiDialog.open( panel, panel, opener );
				return;
			}
			if ( event.target.closest( '[data-se-filters-close]' ) || event.target === panel ) {
				close();
			}
		} );

		panel.addEventListener( 'samadi:close', close );
		document.addEventListener( 'samadi:filters-applied', function () {
			if ( isMobile.matches ) {
				close();
			}
		} );
		isMobile.addEventListener( 'change', function ( mq ) {
			if ( ! mq.matches ) {
				close();
			}
		} );
	}


	/* ---------- Sort menu (links; AJAX-enhanced by the companion plugin) ---------- */

	document.addEventListener( 'click', function ( event ) {
		var toggle = event.target.closest( '.se-sort__toggle' );
		document.querySelectorAll( '[data-se-sort]' ).forEach( function ( sort ) {
			var btn = sort.querySelector( '.se-sort__toggle' );
			var list = sort.querySelector( '.se-sort__list' );
			if ( toggle && sort.contains( toggle ) ) {
				var open = list.hidden;
				list.hidden = ! open;
				btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				if ( open ) {
					var active = list.querySelector( '.is-active' ) || list.querySelector( 'a' );
					if ( active ) {
						active.focus();
					}
				}
			} else if ( ! sort.contains( event.target ) || event.target.closest( '.se-sort__link' ) ) {
				list.hidden = true;
				btn.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}
		var sort = event.target.closest && event.target.closest( '[data-se-sort]' );
		if ( sort ) {
			sort.querySelector( '.se-sort__list' ).hidden = true;
			var btn = sort.querySelector( '.se-sort__toggle' );
			btn.setAttribute( 'aria-expanded', 'false' );
			btn.focus();
		}
	} );

	/* ---------- Quick filter chips → open the filter panel at the matching group ---------- */

	document.addEventListener( 'click', function ( event ) {
		var chip = event.target.closest( '[data-se-filter-jump]' );
		if ( ! chip ) {
			return;
		}
		var group = document.querySelector( '.se-filter-group[data-taxonomy="' + chip.getAttribute( 'data-se-filter-jump' ).replace( /"/g, '' ) + '"]' );
		var opener = document.querySelector( '[data-se-filters-open]' );
		if ( window.matchMedia( '(max-width: 991px)' ).matches && opener ) {
			opener.click();
		}
		if ( group ) {
			window.setTimeout( function () {
				group.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				group.classList.add( 'is-highlighted' );
				var field = group.querySelector( 'input' );
				if ( field ) {
					field.focus( { preventScroll: true } );
				}
				window.setTimeout( function () {
					group.classList.remove( 'is-highlighted' );
				}, 1600 );
			}, 80 );
		}
	} );

	/* ---------- Product gallery: brand tile in the thumbnail column + "1 / N" counter ---------- */

	function initGallery() {
		var gallery = document.querySelector( '.se-single-product .woocommerce-product-gallery' );
		if ( ! gallery ) {
			return;
		}
		var brand = document.querySelector( '.se-single-product .se-gallery-brand' );
		if ( brand && brand.parentNode !== gallery ) {
			gallery.appendChild( brand );
			gallery.classList.add( 'has-brand' );
		}

		var thumbs = gallery.querySelector( '.flex-control-thumbs' );
		if ( ! thumbs ) {
			// Slider initialises after images load; retry briefly.
			if ( ! gallery.__seTries || gallery.__seTries < 20 ) {
				gallery.__seTries = ( gallery.__seTries || 0 ) + 1;
				window.setTimeout( initGallery, 250 );
			}
			return;
		}
		if ( gallery.__seCounter ) {
			return;
		}
		gallery.classList.add( 'has-thumbs' );
		var images = thumbs.querySelectorAll( 'img' );
		var counter = document.createElement( 'span' );
		counter.className = 'se-gallery-counter';
		counter.setAttribute( 'aria-hidden', 'true' );
		gallery.appendChild( counter );
		gallery.__seCounter = counter;

		var digits = function ( n ) {
			return window.samadiTheme && window.samadiTheme.persianDigits ? String( n ).replace( /\d/g, function ( d ) {
				return '۰۱۲۳۴۵۶۷۸۹'.charAt( +d );
			} ) : String( n );
		};
		var update = function () {
			var index = Array.prototype.findIndex.call( images, function ( img ) {
				return img.classList.contains( 'flex-active' );
			} );
			counter.textContent = digits( Math.max( 0, index ) + 1 ) + ' / ' + digits( images.length );
		};
		new MutationObserver( update ).observe( thumbs, { attributes: true, subtree: true, attributeFilter: [ 'class' ] } );
		update();
	}

	/* ---------- Variable products: one price — the selected variation updates the main price ---------- */

	function initVariationPrice() {
		if ( ! window.jQuery ) {
			return;
		}
		var $ = window.jQuery;
		$( '.se-single-product form.variations_form' ).each( function () {
			var form = this;
			var summary = form.closest( '.summary' );
			var priceEl = summary ? summary.querySelector( 'p.price' ) : null;
			if ( ! priceEl ) {
				return;
			}
			var original = priceEl.innerHTML;
			form.classList.add( 'se-price-sync' );
			$( form )
				.on( 'found_variation', function ( e, variation ) {
					if ( variation && variation.price_html ) {
						var tmp = document.createElement( 'div' );
						tmp.innerHTML = variation.price_html;
						var inner = tmp.querySelector( '.price' );
						priceEl.innerHTML = inner ? inner.innerHTML : tmp.innerHTML;
					}
				} )
				.on( 'reset_data', function () {
					priceEl.innerHTML = original;
				} );
			// A default variation may already be selected before this handler was attached.
			var idField = form.querySelector( 'input.variation_id' );
			if ( idField && idField.value ) {
				$( form ).trigger( 'check_variations' );
			}
		} );
	}

	/* ---------- Init ---------- */

	function init() {
		enhanceQuantities();
		initSwatches();
		initBuyBar();
		initFilters();
		initGallery();
		initVariationPrice();
		applyView( storedView() );

		// Cart/checkout fragments and AJAX-loaded product lists replace markup: re-enhance.
		var pending = false;
		new MutationObserver( function () {
			if ( pending ) {
				return;
			}
			pending = true;
			window.requestAnimationFrame( function () {
				pending = false;
				enhanceQuantities();
				initSwatches();
			} );
		} ).observe( document.body, { childList: true, subtree: true } );

		document.addEventListener( 'samadi:results-updated', function () {
			applyView( storedView() );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
