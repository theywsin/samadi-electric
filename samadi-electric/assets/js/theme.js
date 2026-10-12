/**
 * Samadi Electric — general UI: sticky header state and screen-reader announcements.
 */
( function () {
	'use strict';

	var settings = window.samadiTheme || { i18n: {} };

	/**
	 * Announce a message through the polite live region.
	 *
	 * @param {string} message Message.
	 */
	function announce( message ) {
		var region = document.getElementById( 'se-live' );
		if ( ! region || ! message ) {
			return;
		}
		region.textContent = '';
		window.setTimeout( function () {
			region.textContent = message;
		}, 60 );
	}

	window.samadiAnnounce = announce;

	/* ---------- Sticky header shadow ---------- */

	var header = document.querySelector( '[data-se-header]' );
	if ( header && document.body.classList.contains( 'se-has-sticky-header' ) ) {
		var ticking = false;
		var update = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
			ticking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );
		update();
	}

	/* ---------- Header: mobile search toggle ---------- */

	var searchToggle = document.querySelector( '[data-se-search-toggle]' );
	if ( header && searchToggle ) {
		searchToggle.addEventListener( 'click', function () {
			var open = ! header.classList.contains( 'is-search-open' );
			header.classList.toggle( 'is-search-open', open );
			searchToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( open ) {
				var field = header.querySelector( '.se-header__search input[type="search"]' );
				if ( field ) {
					field.focus();
				}
			}
		} );
	}

	/* ---------- Back (product page) and share ---------- */

	document.addEventListener( 'click', function ( event ) {
		var back = event.target.closest( '[data-se-back]' );
		if ( back && document.referrer && 0 === document.referrer.indexOf( window.location.origin ) && window.history.length > 1 ) {
			event.preventDefault();
			window.history.back();
			return;
		}

		var share = event.target.closest( '[data-se-share]' );
		if ( ! share ) {
			return;
		}
		var data = { title: share.getAttribute( 'data-title' ) || document.title, url: share.getAttribute( 'data-url' ) || window.location.href };
		if ( navigator.share ) {
			navigator.share( data ).catch( function () {} );
		} else if ( navigator.clipboard ) {
			navigator.clipboard.writeText( data.url ).then( function () {
				announce( settings.i18n.linkCopied );
			} );
		} else {
			window.prompt( settings.i18n.copyLink, data.url );
		}
	} );

	/* ---------- WooCommerce events (jQuery-based) → announcements ---------- */

	if ( window.jQuery ) {
		window.jQuery( document.body )
			.on( 'added_to_cart', function () {
				announce( settings.i18n.addedToCart );
			} )
			.on( 'updated_cart_totals', function () {
				announce( settings.i18n.cartUpdated );
			} );
	}
}() );
