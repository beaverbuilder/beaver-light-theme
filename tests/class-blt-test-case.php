<?php
/**
 * Base test case: every test starts from a builder with nothing configured.
 *
 * @package Beaver_Light_Theme
 */

/**
 * Resets the builder and Themer stand-ins and the theme's request cache.
 */
abstract class BLT_Test_Case extends WP_UnitTestCase {

	/**
	 * Empty builder settings, no Themer layouts, and a cold Global Styles cache.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		FLBuilderModel::$global_settings      = new stdClass();
		FLBuilderGlobalStyles::$settings      = new stdClass();
		FLThemeBuilderLayoutData::$header_ids = array();
		FLThemeBuilderLayoutData::$footer_ids = array();

		// The values cache lives for one request, and each test is a new request.
		$cache = new ReflectionProperty( 'BLT_Global_Styles', 'values' );
		if ( PHP_VERSION_ID < 80100 ) {
			$cache->setAccessible( true );
		}
		$cache->setValue( null, null );
	}
}
