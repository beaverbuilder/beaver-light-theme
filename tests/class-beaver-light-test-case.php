<?php
/**
 * Base test case: every test starts from a builder with nothing configured.
 *
 * @package Beaver_Light
 */

/**
 * Resets the builder, Themer and design system stand-ins and the theme's request caches.
 *
 * The stand-ins load for the whole run, so the theme's "builder not installed"
 * guards are not exercised here; with nothing configured, the output matches.
 */
abstract class Beaver_Light_Test_Case extends WP_UnitTestCase {

	/**
	 * Start from empty builder state.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		self::reset_builder_state();
	}

	/**
	 * Leave empty builder state behind for tests that do not extend this class.
	 *
	 * @return void
	 */
	public function tear_down() {
		self::reset_builder_state();
		parent::tear_down();
	}

	/**
	 * Empty builder settings, no Themer layouts, no design system, and cold request caches.
	 *
	 * @return void
	 */
	private static function reset_builder_state(): void {
		FLBuilderModel::$global_settings      = new stdClass();
		FLBuilderModel::$builder_enabled      = false;
		FLBuilderGlobalStyles::$settings      = new stdClass();
		FLThemeBuilderLayoutData::$header_ids = array();
		FLThemeBuilderLayoutData::$footer_ids = array();

		\FL\DesignSystem\DesignSystem\DesignSystemPostType::$posts  = array();
		\FL\DesignSystem\DesignSystem\DesignSystemPostType::$tokens  = array();

		// The request caches live for one request, and each test is a new request.
		self::clear_request_cache( 'Beaver_Light_Global_Styles', 'values' );
		self::clear_request_cache( 'Beaver_Light_Color_Inference', 'result' );
	}

	/**
	 * Make a post the site default design system, through the option the stand-in resolves.
	 *
	 * @param WP_Post $post Design system post.
	 * @return void
	 */
	protected static function set_default_design_system( WP_Post $post ): void {
		$id = 'ds-' . $post->ID;

		\FL\DesignSystem\DesignSystem\DesignSystemPostType::$posts[ $id ] = $post;
		update_option( 'fl_ds_site_default', $id );
	}

	/**
	 * A fixture's token map.
	 *
	 * @param string $name Fixture name.
	 * @return array
	 */
	protected static function fixture( string $name ): array {
		return json_decode( file_get_contents( __DIR__ . "/fixtures/kits/{$name}.json" ), true );
	}

	/**
	 * Reset a class's private static request cache.
	 *
	 * @param string $class    Class name.
	 * @param string $property Static property holding the cache.
	 * @return void
	 */
	protected static function clear_request_cache( string $class, string $property ): void {
		$cache = new ReflectionProperty( $class, $property );
		if ( PHP_VERSION_ID < 80100 ) {
			$cache->setAccessible( true );
		}
		$cache->setValue( null, null );
	}
}
