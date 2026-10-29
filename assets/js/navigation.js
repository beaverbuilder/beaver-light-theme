/**
 * Mobile menu toggle. No jQuery, deferred, about 1 KB.
 *
 * aria-expanded on the toggle is the single source of truth CSS keys off;
 * Escape closes the panel and returns focus to the toggle.
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

	toggle.addEventListener( 'click', function () {
		setOpen( 'true' !== toggle.getAttribute( 'aria-expanded' ) );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && nav.classList.contains( 'is-open' ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );
} )();
