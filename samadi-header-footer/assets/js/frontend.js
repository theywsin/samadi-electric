/**
 * Samadi Header & Footer — drawer, dropdowns and search toggle (no dependencies).
 */
( function () {
	'use strict';

	var lastFocus = null;

	function openDrawer( drawer, toggle ) {
		if ( drawer.parentNode !== document.body ) {
			document.body.appendChild( drawer ); // Escape transformed/overflow parents.
		}
		lastFocus = toggle;
		drawer.hidden = false;
		document.body.classList.add( 'shf-lock' );
		toggle.setAttribute( 'aria-expanded', 'true' );
		var close = drawer.querySelector( '.shf-drawer__close' );
		if ( close ) {
			close.focus();
		}
	}

	function closeDrawer( drawer ) {
		drawer.hidden = true;
		document.body.classList.remove( 'shf-lock' );
		if ( lastFocus ) {
			lastFocus.setAttribute( 'aria-expanded', 'false' );
			lastFocus.focus();
		}
	}

	function enhanceSubmenus( scope ) {
		scope.querySelectorAll( '.shf-drawer__list li' ).forEach( function ( li ) {
			var sub = li.querySelector( ':scope > .sub-menu, :scope > .children' );
			if ( ! sub || li.querySelector( ':scope > .shf-drawer__sub' ) ) {
				return;
			}
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'shf-drawer__sub';
			btn.setAttribute( 'aria-expanded', 'false' );
			btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg><span class="shf-sr">زیرمنو</span>';
			btn.addEventListener( 'click', function () {
				var open = li.classList.toggle( 'is-open' );
				btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
			li.insertBefore( btn, sub );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var opener = event.target.closest( '[data-shf-drawer-open]' );
		if ( opener ) {
			var drawer = document.getElementById( opener.getAttribute( 'aria-controls' ) );
			if ( drawer ) {
				enhanceSubmenus( drawer );
				openDrawer( drawer, opener );
			}
			return;
		}

		if ( event.target.closest( '[data-shf-drawer-close]' ) ) {
			var open = event.target.closest( '.shf-drawer' );
			if ( open ) {
				closeDrawer( open );
			}
			return;
		}

		// Categories dropdown.
		var dropBtn = event.target.closest( '[data-shf-dropdown] > button' );
		document.querySelectorAll( '[data-shf-dropdown] > button[aria-expanded="true"]' ).forEach( function ( btn ) {
			if ( btn !== dropBtn && ! btn.parentNode.contains( event.target ) ) {
				btn.setAttribute( 'aria-expanded', 'false' );
				btn.nextElementSibling.hidden = true;
			}
		} );
		if ( dropBtn ) {
			var expanded = 'true' === dropBtn.getAttribute( 'aria-expanded' );
			dropBtn.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			dropBtn.nextElementSibling.hidden = expanded;
			return;
		}

		// Search toggle.
		var searchBtn = event.target.closest( '[data-shf-search-toggle]' );
		document.querySelectorAll( '.shf-search--icon.is-open' ).forEach( function ( box ) {
			if ( ! box.contains( event.target ) ) {
				box.classList.remove( 'is-open' );
				box.querySelector( '[data-shf-search-toggle]' ).setAttribute( 'aria-expanded', 'false' );
			}
		} );
		if ( searchBtn ) {
			var box = searchBtn.closest( '.shf-search' );
			var isOpen = box.classList.toggle( 'is-open' );
			searchBtn.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			if ( isOpen ) {
				box.querySelector( 'input[type="search"]' ).focus();
			}
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}
		document.querySelectorAll( '.shf-drawer:not([hidden])' ).forEach( closeDrawer );
		document.querySelectorAll( '[data-shf-dropdown] > button[aria-expanded="true"]' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-expanded', 'false' );
			btn.nextElementSibling.hidden = true;
		} );
		document.querySelectorAll( '.shf-search--icon.is-open' ).forEach( function ( box ) {
			box.classList.remove( 'is-open' );
		} );
	} );

	// Categories open on hover for mouse users.
	document.querySelectorAll( '[data-shf-dropdown]' ).forEach( function ( drop ) {
		var timer;
		drop.addEventListener( 'mouseenter', function () {
			if ( window.matchMedia( '(hover: hover)' ).matches ) {
				clearTimeout( timer );
				drop.firstElementChild.setAttribute( 'aria-expanded', 'true' );
				drop.firstElementChild.nextElementSibling.hidden = false;
			}
		} );
		drop.addEventListener( 'mouseleave', function () {
			if ( window.matchMedia( '(hover: hover)' ).matches ) {
				timer = setTimeout( function () {
					drop.firstElementChild.setAttribute( 'aria-expanded', 'false' );
					drop.firstElementChild.nextElementSibling.hidden = true;
				}, 180 );
			}
		} );
	} );
}() );
