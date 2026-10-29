<?php
/**
 * Tier 2 of the styling chain: values from Beaver Builder Global Styles.
 *
 * Read in PHP because the builder prints its --fl-global-* properties only on
 * pages that carry a builder layout, and a native archive has none. Global
 * Styles is absent from BB Lite, so every method degrades to "no values".
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Global Styles reader.
 */
final class BLT_Global_Styles {

	/**
	 * Request cache for role values.
	 *
	 * @var array|null
	 */
	private static $values = null;

	/**
	 * Whether the Global Styles extension is loaded.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function available(): bool {
		return class_exists( 'FLBuilderGlobalStyles' ) && method_exists( 'FLBuilderGlobalStyles', 'get_settings' );
	}

	/**
	 * Global Styles values keyed by BLT role (see BLT_Styles::roles()).
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public static function values(): array {
		if ( null !== self::$values ) {
			return self::$values;
		}

		self::$values = array();

		if ( ! self::available() ) {
			return self::$values;
		}

		$settings     = FLBuilderGlobalStyles::get_settings( false );
		self::$values = array_merge( self::color_values( $settings ), self::typography_values( $settings ) );

		return self::$values;
	}

	/**
	 * The global color palette as :root custom properties, so color references resolve on native pages.
	 *
	 * @since 0.1.0
	 * @return string CSS, empty when Global Styles is unavailable.
	 */
	public static function palette_css(): string {
		$can_print = self::available() && method_exists( 'FLBuilderGlobalStyles', 'generate_global_colors_css' );

		return $can_print ? (string) FLBuilderGlobalStyles::generate_global_colors_css() : '';
	}

	/**
	 * Map Global Styles color fields to roles.
	 *
	 * @since 0.1.0
	 * @param object $settings Global Styles settings.
	 * @return array
	 */
	private static function color_values( $settings ): array {
		$map    = array(
			'text_color'              => array( 'text' ),
			'h_color'                 => array( 'heading' ),
			'link_color'              => array( 'link', 'primary' ),
			'link_hover_color'        => array( 'link-hover' ),
			'button_color'            => array( 'on-primary' ),
			'button_background'       => array( 'primary' ),
			'button_hover_background' => array( 'primary-hover' ),
		);
		$values = array();

		foreach ( $map as $key => $roles ) {
			$color = self::color( $settings, $key );

			if ( null === $color ) {
				continue;
			}

			foreach ( $roles as $role ) {
				$values[ $role ] = $color;
			}
		}

		return $values;
	}

	/**
	 * Map Global Styles typography fields to roles.
	 *
	 * @since 0.1.0
	 * @param object $settings Global Styles settings.
	 * @return array
	 */
	private static function typography_values( $settings ): array {
		$text    = self::typography( $settings, 'text_typography' );
		$heading = self::typography( $settings, 'h_typography' );
		$values  = array();

		if ( isset( $text['family'] ) ) {
			$values['font-body'] = $text['family'];
		}

		if ( isset( $text['size'] ) ) {
			$values['font-size'] = $text['size'];
		}

		if ( isset( $heading['family'] ) ) {
			$values['font-heading'] = $heading['family'];
		}

		if ( isset( $heading['weight'] ) ) {
			$values['heading-weight'] = $heading['weight'];
		}

		return $values;
	}

	/**
	 * A color field as a CSS value, or null when unset.
	 *
	 * @since 0.1.0
	 * @param object $settings Global Styles settings.
	 * @param string $key      Field key.
	 * @return string|null
	 */
	private static function color( $settings, string $key ): ?string {
		if ( empty( $settings->$key ) ) {
			return null;
		}

		return (string) FLBuilderColor::hex_or_rgb( $settings->$key );
	}

	/**
	 * A typography field as family/weight/size CSS values; unset parts are omitted.
	 *
	 * @since 0.1.0
	 * @param object $settings Global Styles settings.
	 * @param string $key      Field key.
	 * @return array
	 */
	private static function typography( $settings, string $key ): array {
		$typography = ! empty( $settings->$key ) ? (array) $settings->$key : array();
		$family     = $typography['font_family'] ?? '';
		$weight     = $typography['font_weight'] ?? '';
		$size       = $typography['font_size']['length'] ?? '';
		$out        = array();

		if ( '' !== $family && 'Default' !== $family ) {
			$quoted        = preg_match( '/[\s0-9]/', $family ) ? '"' . $family . '"' : $family;
			$out['family'] = $quoted . ', ' . FLBuilderFonts::get_font_fallback( $family );
		}

		if ( '' !== $weight && 'default' !== $weight ) {
			$out['weight'] = rtrim( (string) $weight, 'i' );
		}

		if ( '' !== $size ) {
			$out['size'] = $size . ( $typography['font_size']['unit'] ?? 'px' );
		}

		return $out;
	}
}
