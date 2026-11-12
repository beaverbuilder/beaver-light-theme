<?php
/**
 * Stand-ins for the Beaver Themer classes the theme calls.
 *
 * The layout lists come from public static state a test sets, and the
 * renderers print a marker so a test can see where they ran.
 *
 * @package Beaver_Light_Theme
 */

/**
 * Themer layout data stand-in.
 */
class FLThemeBuilderLayoutData {

	/**
	 * Header layout IDs that apply to the current page.
	 *
	 * @var int[]
	 */
	public static $header_ids = array();

	/**
	 * Footer layout IDs that apply to the current page.
	 *
	 * @var int[]
	 */
	public static $footer_ids = array();

	/**
	 * Header layouts for the current page.
	 *
	 * @return int[]
	 */
	public static function get_current_page_header_ids() {
		return self::$header_ids;
	}

	/**
	 * Footer layouts for the current page.
	 *
	 * @return int[]
	 */
	public static function get_current_page_footer_ids() {
		return self::$footer_ids;
	}
}

/**
 * Themer renderer stand-in.
 */
class FLThemeBuilderLayoutRenderer {

	/**
	 * Print the header marker.
	 *
	 * @return void
	 */
	public static function render_header() {
		echo '[themer-header]';
	}

	/**
	 * Print the footer marker.
	 *
	 * @return void
	 */
	public static function render_footer() {
		echo '[themer-footer]';
	}
}
