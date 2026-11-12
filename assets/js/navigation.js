/**
 * Mobile menu toggle and submenu dismissal. No jQuery, deferred, about 1 KB.
 *
 * aria-expanded on the toggle is the single source of truth CSS keys off;
 * Escape closes the panel and returns focus to the toggle. On wide screens,
 * where submenus open on hover and focus, Escape dismisses the open submenu
 * instead (WCAG 1.4.13) until both focus and the pointer have left its item.
 */
( function () {
	var toggle = document.querySelector( '.blt-nav-toggle' );
	var nav = document.getElementById( 'blt-nav' );
	var parentItem = '.blt-menu > .menu-item-has-children';

	if ( ! toggle || ! nav ) {
		return;
	}

	function setOpen( open ) {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
	}

	function dismissSubmenu() {
		var active = document.activeElement;
		var focused = active && nav.contains( active ) ? active.closest( parentItem ) : null;
		var item = focused || nav.querySelector( parentItem + ':hover' );
		var link = item ? item.querySelector( 'a[href]' ) : null;

		if ( ! item ) {
			return;
		}

		item.classList.add( 'is-dismissed' );

		// Pull focus out of the hidden submenu; a hover-only dismissal leaves focus where it is.
		if ( focused && link ) {
			link.focus();
		}
	}

	function restoreSubmenu( event ) {
		var item = event.target.closest( '.is-dismissed' );

		if ( ! item || item.contains( event.relatedTarget ) ) {
			return;
		}

		// Focus leaving checks the pointer, and the pointer leaving checks focus: both must be gone.
		var otherStillIn = 'focusout' === event.type ? item.matches( ':hover' ) : item.contains( document.activeElement );

		if ( ! otherStillIn ) {
			item.classList.remove( 'is-dismissed' );
		}
	}

	toggle.addEventListener( 'click', function () {
		setOpen( 'true' !== toggle.getAttribute( 'aria-expanded' ) );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		// The toggle is hidden on wide screens, where a panel left open by a resize does not apply.
		var panelOpen = nav.classList.contains( 'is-open' ) && toggle.offsetWidth > 0;

		if ( 'Escape' !== event.key ) {
			return;
		}

		if ( panelOpen ) {
			setOpen( false );
			toggle.focus();
			return;
		}

		dismissSubmenu();
	} );

	nav.addEventListener( 'focusout', restoreSubmenu );
	nav.addEventListener( 'mouseout', restoreSubmenu );
} )();
