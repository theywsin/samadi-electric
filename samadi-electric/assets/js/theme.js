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
