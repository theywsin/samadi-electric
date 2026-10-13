/**
 * Samadi Account: wishlist hearts, confirmations and the checkout address picker.
 */
( function () {
	'use strict';

	var cfg = window.sadAccount || {};

	function announce( text ) {
		var live = document.getElementById( 'sad-live' );
		if ( ! live ) {
			live = document.createElement( 'div' );
			live.id = 'sad-live';
			live.className = 'sad-sr';
			live.setAttribute( 'aria-live', 'polite' );
			document.body.appendChild( live );
		}
		live.textContent = '';
		window.setTimeout( function () {
			live.textContent = text;
		}, 50 );
	}

	/* ---------- Wishlist ---------- */

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest( '[data-sad-wish]' );
		if ( ! btn ) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();

		if ( ! cfg.loggedIn ) {
			window.location.href = cfg.loginUrl;
			return;
		}
		if ( btn.disabled ) {
			return;
		}
		btn.disabled = true;

		var body = new FormData();
		body.append( 'action', 'sad_wishlist_toggle' );
		body.append( 'nonce', cfg.nonce );
		body.append( 'product', btn.getAttribute( 'data-sad-wish' ) );

		fetch( cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( json ) {
				if ( ! json || ! json.success ) {
					return;
				}
				var active = json.data.active;
				document.querySelectorAll( '[data-sad-wish="' + btn.getAttribute( 'data-sad-wish' ) + '"]' ).forEach( function ( el ) {
					el.classList.toggle( 'is-active', active );
					el.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				} );
				announce( active ? cfg.i18n.added : cfg.i18n.removed );

				// On the account pages, removing a card takes it off the list.
				if ( ! active && btn.closest( '.sad-account .sad-product' ) ) {
					var card = btn.closest( '.sad-product' );
					card.style.opacity = '0';
					window.setTimeout( function () {
						card.remove();
					}, 200 );
				}
			} )
			.finally( function () {
				btn.disabled = false;
			} );
	} );

	/* ---------- Confirm destructive links ---------- */

	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest( '[data-sad-confirm]' );
		if ( link && ! window.confirm( cfg.i18n.confirm ) ) {
			event.preventDefault();
		}
	} );

	/* ---------- Checkout address picker ---------- */

	var picker = document.querySelector( '.sad-picker' );
	if ( picker ) {
		var data = {};
		try {
			data = JSON.parse( picker.querySelector( '.sad-picker__data' ).textContent );
		} catch ( e ) {}

		picker.addEventListener( 'change', function ( event ) {
			if ( 'block' === picker.getAttribute( 'data-mode' ) ) {
				// Block checkout reads the customer from the server: save the choice and reload.
				var body = new FormData();
				body.append( 'action', 'sad_pick_address' );
				body.append( 'nonce', picker.getAttribute( 'data-nonce' ) );
				body.append( 'id', event.target.value );
				picker.classList.add( 'is-loading' );
				fetch( cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body } ).then( function () {
					window.location.reload();
				} );
				return;
			}
			var values = data[ event.target.value ];
			if ( ! values ) {
				return;
			}
			Object.keys( values ).forEach( function ( name ) {
				var field = document.querySelector( '[name="' + name + '"]' );
				if ( ! field ) {
					return;
				}
				field.value = values[ name ];
				field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				if ( window.jQuery ) {
					window.jQuery( field ).trigger( 'change' );
				}
			} );
			if ( window.jQuery ) {
				window.jQuery( document.body ).trigger( 'update_checkout' );
			}
		} );
	}
}() );
