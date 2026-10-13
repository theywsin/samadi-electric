/**
 * Samadi Core — wishlist, compare and newsletter (no dependencies).
 *
 * Button states are read from cookies on every page load, so they stay right on cached pages.
 */
( function () {
	'use strict';

	var cfg = window.samadiCore || {};
	var i18n = cfg.i18n || {};

	/* ---------- Helpers ---------- */

	function digits( value ) {
		var str = String( value );
		return cfg.persianDigits ? str.replace( /\d/g, function ( d ) {
			return '۰۱۲۳۴۵۶۷۸۹'.charAt( +d );
		} ) : str;
	}

	function latinDigits( value ) {
		return String( value ).replace( /[۰-۹]/g, function ( d ) {
			return String( d.charCodeAt( 0 ) - 0x06f0 );
		} ).replace( /[٠-٩]/g, function ( d ) {
			return String( d.charCodeAt( 0 ) - 0x0660 );
		} );
	}

	function readIds( name ) {
		var match = document.cookie.match( new RegExp( '(?:^|;\\s*)' + name + '=([^;]*)' ) );
		if ( ! match ) {
			return [];
		}
		return decodeURIComponent( match[ 1 ] ).split( '.' ).map( Number ).filter( function ( id, i, all ) {
			return id > 0 && all.indexOf( id ) === i;
		} );
	}

	function writeIds( name, ids ) {
		document.cookie = name + '=' + ids.join( '.' ) +
			'; path=' + ( cfg.cookiePath || '/' ) +
			'; max-age=' + ( ids.length ? 31536000 : 0 ) +
			'; SameSite=Lax' + ( cfg.secure ? '; Secure' : '' );
	}

	function each( selector, callback, root ) {
		Array.prototype.forEach.call( ( root || document ).querySelectorAll( selector ), callback );
	}

	function request( path, data ) {
		var headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
		if ( cfg.nonce ) {
			headers[ 'X-WP-Nonce' ] = cfg.nonce;
		}
		return fetch( cfg.rest + path, {
			method: 'POST',
			credentials: 'same-origin',
			headers: headers,
			body: JSON.stringify( data ),
		} ).then( function ( response ) {
			return response.json().catch( function () {
				return {};
			} ).then( function ( body ) {
				if ( ! response.ok ) {
					var error = new Error( body && body.message ? body.message : i18n.error );
					error.body = body;
					throw error;
				}
				return body;
			} );
		} );
	}

	/* ---------- Toast ---------- */

	var toastBox = null;
	var toastTimer = 0;

	function toast( message, linkText, linkUrl ) {
		if ( ! toastBox ) {
			toastBox = document.createElement( 'div' );
			toastBox.className = 'se-core-toast';
			toastBox.setAttribute( 'role', 'status' );
			toastBox.setAttribute( 'aria-live', 'polite' );
			document.body.appendChild( toastBox );
		}

		var msg = document.createElement( 'div' );
		msg.className = 'se-core-toast__msg';
		msg.appendChild( document.createTextNode( message ) );

		if ( linkText && linkUrl ) {
			var link = document.createElement( 'a' );
			link.href = linkUrl;
			link.textContent = linkText;
			msg.appendChild( link );
		}

		toastBox.innerHTML = '';
		toastBox.appendChild( msg );
		toastBox.classList.remove( 'is-visible' );
		// Next frame so the transition runs.
		window.requestAnimationFrame( function () {
			toastBox.classList.add( 'is-visible' );
		} );

		window.clearTimeout( toastTimer );
		toastTimer = window.setTimeout( function () {
			toastBox.classList.remove( 'is-visible' );
		}, 3200 );
	}

	/* ---------- Shared button state ---------- */

	function setPressed( button, active ) {
		button.classList.toggle( 'is-active', active );
		button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );

		var label = active ? button.getAttribute( 'data-label-remove' ) : button.getAttribute( 'data-label-add' );
		var text = button.querySelector( '.se-core-action__label' );

		if ( text ) {
			text.textContent = label;
		} else if ( label ) {
			button.setAttribute( 'aria-label', label );
			button.setAttribute( 'title', label );
		}
	}

	/* ---------- Wishlist ---------- */

	var wish = cfg.wishlist;

	function wishIds() {
		return wish ? readIds( wish.cookie ) : [];
	}

	function wishBadges( count ) {
		if ( ! wish ) {
			return;
		}

		var hosts = [];
		each( '.se-header__wishlist', function ( el ) {
			hosts.push( el );
		} );
		if ( wish.url ) {
			each( '.se-bottom-nav__link', function ( el ) {
				if ( el.href === wish.url ) {
					hosts.push( el.querySelector( '.se-bottom-nav__icon' ) || el );
				}
			} );
		}

		hosts.forEach( function ( host ) {
			var badge = host.querySelector( '.se-core-wish-count' );
			if ( ! badge ) {
				badge = document.createElement( 'span' );
				badge.className = 'se-cart-count se-core-wish-count';
				badge.setAttribute( 'aria-hidden', 'true' );
				host.appendChild( badge );
			}
			badge.textContent = digits( count );
			badge.classList.toggle( 'is-empty', ! count );
		} );
	}

	function wishSync( ids ) {
		ids = ids || wishIds();
		each( '[data-se-wish]', function ( button ) {
			setPressed( button, ids.indexOf( +button.getAttribute( 'data-se-wish' ) ) !== -1 );
		} );
		wishBadges( ids.length );
	}

	function wishRemoveCard( button ) {
		var page = button.closest( '[data-se-wishlist-page]' );
		var card = page && button.closest( 'li.product, .product' );
		if ( ! card ) {
			return;
		}

		card.classList.add( 'is-removing' );
		window.setTimeout( function () {
			card.remove();
			var left = page.querySelectorAll( 'li.product' ).length;
			var total = page.querySelector( '[data-se-wishlist-total]' );
			if ( total ) {
				total.textContent = digits( left );
			}
			if ( ! left ) {
				each( 'ul.products, .se-core-wishlist__count', function ( el ) {
					el.remove();
				}, page );
				var empty = page.querySelector( '[data-se-wishlist-empty]' );
				if ( empty ) {
					empty.hidden = false;
				}
			}
		}, 250 );
	}

	function wishToggle( button ) {
		var id = +button.getAttribute( 'data-se-wish' );
		var wasActive = button.classList.contains( 'is-active' );
		var group = document.querySelectorAll( '[data-se-wish="' + id + '"]' );

		Array.prototype.forEach.call( group, function ( el ) {
			setPressed( el, ! wasActive );
			el.classList.add( 'is-busy' );
		} );
		if ( ! wasActive ) {
			button.classList.add( 'is-pop' );
		}

		request( 'wishlist', { product_id: id, action: wasActive ? 'remove' : 'add' } ).then( function ( data ) {
			wishSync( data.ids || [] );
			if ( data.added ) {
				toast( wish.i18n.added, wish.i18n.view, wish.url );
			} else {
				toast( wish.i18n.removed );
				wishRemoveCard( button );
			}
			document.dispatchEvent( new CustomEvent( 'samadi:wishlist', { detail: data } ) );
		} ).catch( function ( error ) {
			Array.prototype.forEach.call( group, function ( el ) {
				setPressed( el, wasActive );
			} );
			toast( error.message || i18n.error );
		} ).then( function () {
			Array.prototype.forEach.call( group, function ( el ) {
				el.classList.remove( 'is-busy' );
			} );
			window.setTimeout( function () {
				button.classList.remove( 'is-pop' );
			}, 400 );
		} );
	}

	/* ---------- Compare ---------- */

	var cmp = cfg.compare;

	function cmpIds() {
		return cmp ? readIds( cmp.cookie ).slice( 0, cmp.max ) : [];
	}

	function cmpSync() {
		if ( ! cmp ) {
			return;
		}

		var ids = cmpIds();
		each( '[data-se-compare]:not([data-se-compare-remove])', function ( button ) {
			setPressed( button, ids.indexOf( +button.getAttribute( 'data-se-compare' ) ) !== -1 );
		} );

		var bar = document.querySelector( '[data-se-compare-bar]' );
		if ( bar ) {
			bar.hidden = ! ids.length || cmp.isPage;
			each( '[data-se-compare-count]', function ( el ) {
				el.textContent = digits( ids.length );
			}, bar );
		}
	}

	function cmpToggle( button ) {
		var id = +button.getAttribute( 'data-se-compare' );
		var ids = cmpIds();
		var index = ids.indexOf( id );

		if ( button.hasAttribute( 'data-se-compare-remove' ) ) {
			if ( index !== -1 ) {
				ids.splice( index, 1 );
			}
			writeIds( cmp.cookie, ids );
			window.location.reload();
			return;
		}

		if ( index !== -1 ) {
			ids.splice( index, 1 );
			toast( cmp.i18n.removed );
		} else if ( ids.length >= cmp.max ) {
			toast( cmp.i18n.full.replace( '%s', digits( cmp.max ) ), cmp.i18n.view, cmp.url );
			return;
		} else {
			ids.push( id );
			toast( cmp.i18n.added, cmp.i18n.view, cmp.url );
		}

		writeIds( cmp.cookie, ids );
		cmpSync();
		document.dispatchEvent( new CustomEvent( 'samadi:compare', { detail: { ids: ids } } ) );
	}

	function cmpClear() {
		writeIds( cmp.cookie, [] );
		if ( cmp.isPage ) {
			window.location.reload();
			return;
		}
		cmpSync();
	}

	/* ---------- Newsletter ---------- */

	function newsletterMessage( form, text, ok ) {
		var msg = form.querySelector( '.se-core-newsletter__msg' );
		if ( msg ) {
			msg.textContent = text;
			msg.classList.toggle( 'is-success', !! ok );
			msg.classList.toggle( 'is-error', ! ok );
		}
	}

	function newsletterSubmit( form ) {
		var nl = cfg.newsletter || {};
		var input = form.querySelector( '[name="mobile"]' );
		var mobile = latinDigits( input.value ).replace( /[\s\-().]/g, '' );

		if ( ! /^(?:\+98|0098|98|0)?9\d{9}$/.test( mobile ) ) {
			input.setAttribute( 'aria-invalid', 'true' );
			newsletterMessage( form, nl.invalid, false );
			input.focus();
			return;
		}

		input.removeAttribute( 'aria-invalid' );
		form.classList.add( 'is-busy' );
		newsletterMessage( form, nl.sending, true );

		request( 'newsletter', {
			mobile: mobile,
			source: ( form.querySelector( '[name="source"]' ) || {} ).value || '',
			website: ( form.querySelector( '[name="website"]' ) || {} ).value || '',
		} ).then( function ( data ) {
			newsletterMessage( form, data.message, true );
			input.value = '';
		} ).catch( function ( error ) {
			if ( error.body && error.body.message ) {
				newsletterMessage( form, error.body.message, false );
				if ( 'invalid' === error.body.code ) {
					input.setAttribute( 'aria-invalid', 'true' );
				}
				return;
			}
			// Network or REST failure: fall back to a regular form post.
			HTMLFormElement.prototype.submit.call( form );
		} ).then( function () {
			form.classList.remove( 'is-busy' );
		} );
	}

	/* ---------- Events ---------- */

	document.addEventListener( 'click', function ( event ) {
		var target = event.target.closest ? event.target : event.target.parentElement;
		if ( ! target ) {
			return;
		}

		var wishButton = wish && target.closest( '[data-se-wish]' );
		if ( wishButton ) {
			event.preventDefault();
			if ( ! wishButton.classList.contains( 'is-busy' ) ) {
				wishToggle( wishButton );
			}
			return;
		}

		var compareButton = cmp && target.closest( '[data-se-compare]' );
		if ( compareButton ) {
			event.preventDefault();
			cmpToggle( compareButton );
			return;
		}

		if ( cmp && target.closest( '[data-se-compare-clear]' ) ) {
			event.preventDefault();
			cmpClear();
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.matches && event.target.matches( '[data-se-compare-diff]' ) ) {
			var page = event.target.closest( '[data-se-compare-page]' );
			var table = page && page.querySelector( '.se-core-compare-table' );
			if ( table ) {
				table.classList.toggle( 'is-diff-only', event.target.checked );
			}
		}
	} );

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target;
		if ( form.matches && form.matches( '[data-se-newsletter]' ) && window.fetch && cfg.rest ) {
			event.preventDefault();
			newsletterSubmit( form );
		}
	} );

	function syncAll() {
		if ( wish ) {
			wishSync();
		}
		cmpSync();
	}

	// Cards added later (AJAX filters, Elementor) are synced when the Samadi filters or WooCommerce
	// announce them; back/forward navigation restores pages from cache.
	document.addEventListener( 'samadi:results-updated', syncAll );
	window.addEventListener( 'pageshow', function ( event ) {
		if ( event.persisted ) {
			syncAll();
		}
	} );
	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'updated_wc_div wc_fragments_loaded', syncAll );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', syncAll );
	} else {
		syncAll();
	}
}() );
