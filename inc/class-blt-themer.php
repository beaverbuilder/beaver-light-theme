<?php
/**
 * Beaver Themer self-registration: support flags, the Parts hook map, and
 * the header/footer handoff.
 *
 * Registration is unconditional because Themer classes load lazily; the hooks
 * are inert without Themer and the wp-time handoff guards itself.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Themer integration.
 */
final class BLT_Themer {

	/**
	 * Wire Themer hooks.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		add_action( 'after_setup_theme', array( __CLASS__, 'add_support' ) );
		add_filter( 'fl_theme_builder_part_hooks', array( __CLASS__, 'register_part_hooks' ) );
		add_action( 'wp', array( __CLASS__, 'setup_headers_and_footers' ) );
	}

	/**
	 * Declare Themer support for headers, footers and Parts.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function add_support(): void {
		add_theme_support( 'fl-theme-builder-headers' );
		add_theme_support( 'fl-theme-builder-footers' );
		add_theme_support( 'fl-theme-builder-parts' );
	}

	/**
	 * Register the canonical hook map with the Parts UI; fl_* aliases fire silently.
	 *
	 * @since 0.1.0
	 * @param array $groups Hook groups registered so far.
	 * @return array
	 */
	public static function register_part_hooks( $groups ): array {
		$groups = is_array( $groups ) ? $groups : array();

		foreach ( BLT_Hooks::map() as $label => $hooks ) {
			$groups[] = array(
				'label' => $label,
				'hooks' => $hooks,
			);
		}

		return $groups;
	}

	/**
	 * Swap the native header/footer renderers for Themer layouts when any apply.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function setup_headers_and_footers(): void {
		if ( ! class_exists( 'FLThemeBuilderLayoutData' ) || ! class_exists( 'FLThemeBuilderLayoutRenderer' ) ) {
			return;
		}

		$header_ids = FLThemeBuilderLayoutData::get_current_page_header_ids();
		$footer_ids = FLThemeBuilderLayoutData::get_current_page_footer_ids();

		if ( ! empty( $header_ids ) ) {
			remove_action( 'blt_header', array( 'BLT_Chrome', 'render_header' ) );
			add_action( 'blt_header', 'FLThemeBuilderLayoutRenderer::render_header' );
		}

		if ( ! empty( $footer_ids ) ) {
			remove_action( 'blt_footer', array( 'BLT_Chrome', 'render_footer' ) );
			add_action( 'blt_footer', array( __CLASS__, 'render_themer_footer' ) );
		}
	}

	/**
	 * Render the Themer footer inside the footer open/close positions so Parts pinned there survive.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function render_themer_footer(): void {
		BLT_Hooks::fire( 'blt_footer_open' );
		FLThemeBuilderLayoutRenderer::render_footer();
		BLT_Hooks::fire( 'blt_footer_close' );
	}
}
