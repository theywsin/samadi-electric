/**
 * Samadi Electric — navigation: submenus, mega menu, off-canvas drawer and a shared dialog helper.
 * Vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	var FOCUSABLE = 'a[href], area[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	/**
	 * Accessible modal panel helper (focus trap, Escape, scroll lock, focus restore).
	 */
	var Dialog = {
		stack: [],

		open: function ( root, panel, opener, onClose ) {
			var entry = { root: root, panel: panel || root, opener: opener || document.activeElement, onClose: onClose };
			this.stack.push( entry );
			document.body.classList.add( 'se-scroll-lock' );

			if ( opener ) {
				opener.setAttribute( 'aria-expanded', 'true' );
			}

			var focusTarget = entry.panel.querySelector( '[autofocus]' ) || entry.panel;
			window.requestAnimationFrame( function () {
				focusTarget.focus( { preventScroll: true } );
			} );
		},

		close: function ( root ) {
			var index = -1;
			this.stack.forEach( function ( item, i ) {
				if ( item.root === root ) {
					index = i;
				}
			} );
			if ( -1 === index ) {
				return;
			}
			var entry = this.stack.splice( index, 1 )[ 0 ];
			if ( ! this.stack.length ) {
				document.body.classList.remove( 'se-scroll-lock' );
			}
			if ( entry.opener && entry.opener.setAttribute ) {
				entry.opener.setAttribute( 'aria-expanded', 'false' );
				entry.opener.focus( { preventScroll: true } );
			}
			if ( 'function' === typeof entry.onClose ) {
				entry.onClose();
			}
		},

		top: function () {
			return this.stack[ this.stack.length - 1 ];
		}
	};

	document.addEventListener( 'keydown', function ( event ) {
		var current = Dialog.top();
		if ( ! current ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			event.preventDefault();
			current.root.dispatchEvent( new CustomEvent( 'samadi:close' ) );
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var nodes = Array.prototype.filter.call( current.panel.querySelectorAll( FOCUSABLE ), function ( el ) {
			return el.offsetParent !== null;
		} );
		if ( ! nodes.length ) {
			event.preventDefault();
			return;
		}
		var first = nodes[ 0 ];
		var last = nodes[ nodes.length - 1 ];

		if ( event.shiftKey && ( document.activeElement === first || document.activeElement === current.panel ) ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	window.SamadiDialog = Dialog;

	/* ---------- Submenu toggles (desktop dropdowns + drawer accordions) ---------- */

	document.addEventListener( 'click', function ( event ) {
		var toggle = event.target.closest( '.se-submenu-toggle' );
		if ( ! toggle ) {
			return;
		}
		var item = toggle.closest( '.menu-item' );
		if ( ! item ) {
			return;
		}
		var open = ! item.classList.contains( 'is-open' );
		item.classList.toggle( 'is-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );

	/* ---------- Mega menu ---------- */

	var mega = document.querySelector( '.se-header [data-se-mega]' );
	if ( mega ) {
		mega.setAttribute( 'data-se-mega-ready', '' );
		var megaToggle = mega.querySelector( '.se-mega__toggle' );
		var megaPanel = mega.querySelector( '.se-mega__panel' );
		var hoverTimer;

		var setMega = function ( open ) {
			megaPanel.hidden = ! open;
			megaToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		};

		megaToggle.addEventListener( 'click', function () {
			setMega( megaPanel.hidden );
		} );

		if ( window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			[ megaToggle, megaPanel ].forEach( function ( el ) {
				el.addEventListener( 'mouseenter', function () {
					window.clearTimeout( hoverTimer );
					setMega( true );
				} );
				el.addEventListener( 'mouseleave', function () {
					hoverTimer = window.setTimeout( function () {
						setMega( false );
					}, 180 );
				} );
			} );
		}

		document.addEventListener( 'click', function ( event ) {
			if ( ! megaPanel.hidden && ! mega.contains( event.target ) ) {
				setMega( false );
			}
		} );

		mega.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! megaPanel.hidden ) {
				setMega( false );
				megaToggle.focus();
			}
		} );

		mega.addEventListener( 'focusout', function ( event ) {
			if ( event.relatedTarget && ! mega.contains( event.relatedTarget ) ) {
				setMega( false );
			}
		} );
	}

	/* ---------- Drawer ---------- */

	var drawer = document.querySelector( '[data-se-drawer]' );
	if ( drawer ) {
		var panel = drawer.querySelector( '.se-drawer__panel' );

		var closeDrawer = function () {
			drawer.hidden = true;
			Dialog.close( drawer );
		};

		document.addEventListener( 'click', function ( event ) {
			var opener = event.target.closest( '[data-se-drawer-open]' );
			if ( opener ) {
				drawer.hidden = false;
				Dialog.open( drawer, panel, opener );
				return;
			}
			if ( event.target.closest( '[data-se-drawer-close]' ) && drawer.contains( event.target ) ) {
				closeDrawer();
			}
		} );

		drawer.addEventListener( 'samadi:close', closeDrawer );

		// Close when switching to desktop layout.
		window.matchMedia( '(min-width: 992px)' ).addEventListener( 'change', function ( mq ) {
			if ( mq.matches && ! drawer.hidden ) {
				closeDrawer();
			}
		} );
	}
}() );
