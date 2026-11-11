<?php
/**
 * Nav menu locations, part of the theme's public surface (CONTRIBUTING.md).
 *
 * @package Beaver_Light_Theme
 */

/**
 * The theme registers exactly the `header` and `footer` menu locations.
 */
class Test_Menu_Locations extends WP_UnitTestCase {

	/**
	 * Both locations are registered after the theme loads, and no others are.
	 */
	public function test_header_and_footer_are_the_only_locations() {
		$locations = array_keys( get_registered_nav_menus() );

		$this->assertEqualsCanonicalizing( array( 'header', 'footer' ), $locations );
	}
}
