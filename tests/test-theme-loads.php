<?php
/**
 * Smoke test: the theme activates and functions.php loads.
 *
 * @package Beaver_Light_Theme
 */

/**
 * Proves the harness runs against the theme.
 */
class Test_Theme_Loads extends WP_UnitTestCase {

	/**
	 * The repo folder is the active theme.
	 */
	public function test_theme_is_active() {
		$this->assertSame( basename( dirname( __DIR__ ) ), get_stylesheet() );
	}

	/**
	 * after_setup_theme ran, so the theme's setup callback was registered and fired.
	 */
	public function test_setup_ran() {
		$this->assertGreaterThan( 0, did_action( 'after_setup_theme' ) );
		$this->assertTrue( current_theme_supports( 'post-thumbnails' ) );
	}

	/**
	 * functions.php defined the version constant.
	 */
	public function test_version_constant_is_defined() {
		$this->assertTrue( defined( 'BLT_VERSION' ) );
		$this->assertNotSame( '', BLT_VERSION );
	}
}
