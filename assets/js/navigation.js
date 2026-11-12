/**
 * Mobile menu toggle and submenu dismissal. No jQuery, deferred, about 1 KB.
 *
 * aria-expanded on the toggle is the single source of truth CSS keys off;
 * Escape closes the panel and returns focus to the toggle. On wide screens,
 * where submenus open on hover and focus, Escape dismisses the open submenu
 * instead (WCAG 1.4.13) until focus or the pointer leaves its item.
 */
( function () {
	var toggle = document.querySelector( '.blt-nav-toggle' );
	var nav = document.getElementById( 'blt-nav' );

	if ( ! toggle || ! nav ) {
		return;
	}

	function setOpen( open ) {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
	}

	function dismissSubmenu() {
		var active = document.activeElement;
		var item = active && nav.contains( active ) ? active.closest( '.blt-menu > .menu-item-has-children' ) : null;

		if ( ! item ) {
			return;
		}

		item.classList.add( 'is-dismissed' );
		item.querySelector( 'a' ).focus();
	}

	function restoreSubmenu( event ) {
		var item = event.target.closest( '.is-dismissed' );

		if ( item && ! item.contains( event.relatedTarget ) ) {
			item.classList.remove( 'is-dismissed' );
		}
	}

	toggle.addEventListener( 'click', function () {
		setOpen( 'true' !== toggle.getAttribute( 'aria-expanded' ) );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}

		if ( nav.classList.contains( 'is-open' ) ) {
			setOpen( false );
			toggle.focus();
			return;
		}

		dismissSubmenu();
	} );

	nav.addEventListener( 'focusout', restoreSubmenu );
	nav.addEventListener( 'mouseout', restoreSubmenu );
} )();
