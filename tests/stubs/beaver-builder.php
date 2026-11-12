<?php
/**
 * Stand-ins for the Beaver Builder classes the theme calls.
 *
 * Each answers from public static state a test sets, so the suite runs without
 * the plugin. Only the methods the theme calls exist.
 *
 * @package Beaver_Light_Theme
 */

/**
 * Builder model stand-in.
 */
class FLBuilderModel {

	/**
	 * What get_global_settings() returns.
	 *
	 * @var object
	 */
	public static $global_settings;

	/**
	 * The builder's global settings.
	 *
	 * @return object
	 */
	public static function get_global_settings() {
		return self::$global_settings;
	}
}

/**
 * Global Styles stand-in.
 */
class FLBuilderGlobalStyles {

	/**
	 * What get_settings() returns.
	 *
	 * @var object
	 */
	public static $settings;

	/**
	 * The Global Styles settings.
	 *
	 * @param bool $filter Ignored.
	 * @return object
	 */
	public static function get_settings( $filter = true ) {
		return self::$settings;
	}
}

/**
 * Color helper stand-in, matching the builder's handling of bare hex values.
 */
class FLBuilderColor {

	/**
	 * A stored color as CSS: bare hex gets a hash, rgb() and var() pass through.
	 *
	 * @param string $value Stored color.
	 * @return string
	 */
	public static function hex_or_rgb( $value ) {
		if ( false !== strpos( $value, 'rgb' ) || false !== strpos( $value, 'var' ) ) {
			return $value;
		}

		return '#' . ltrim( $value, '#' );
	}
}

/**
 * Font helper stand-in.
 */
class FLBuilderFonts {

	/**
	 * The generic family appended after a font.
	 *
	 * @param string $family Font family.
	 * @return string
	 */
	public static function get_font_fallback( $family ) {
		return 'sans-serif';
	}
}
