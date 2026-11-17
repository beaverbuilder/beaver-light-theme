<?php
/**
 * Nav menu locations, part of the theme's public surface (CONTRIBUTING.md).
 *
 * @package Beaver_Light
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

	/**
	 * The header label tells site owners in Appearance > Menus that only two levels render.
	 */
	public function test_header_label_names_the_two_level_limit() {
		$locations = get_registered_nav_menus();

		$this->assertSame( 'Header Menu (two levels)', $locations['header'] );
	}
}
