<?php
/**
 * The site default design system's font families, resolved in PHP for the places its CSS does not reach.
 *
 * The design system sends its tokens to a block editor only when the editor has
 * a post, and the Style Book has none, so there --ds-font-* is undefined and the
 * bridge would fall through to the theme default.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * Design system font families and the theme.json feed for the Style Book.
 */
final class Beaver_Light_Design_Fonts {

	/**
	 * A font family list: comma-separated names, each bare or wholly quoted, so a stored value cannot break out of a declaration.
	 */
	const FAMILY = '/^\s*(?:"[\w .-]+"|\'[\w .-]+\'|[\w .-]+)\s*(?:,\s*(?:"[\w .-]+"|\'[\w .-]+\'|[\w .-]+)\s*)*$/uD';

	/**
	 * The font roles this resolves (see Beaver_Light_Styles::roles()).
	 */
	const ROLES = array( 'font-body', 'font-heading' );

	/**
	 * Request cache for the resolved families.
	 *
	 * @var array|null
	 */
	private static $values = null;

	/**
	 * Wire the theme.json font feed.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'wp_theme_json_data_theme', array( __CLASS__, 'theme_json_fonts' ) );
	}

	/**
	 * The font roles' families as the site default design system defines them.
	 *
	 * @since 0.1.0
	 * @return array Role => font family list; a role is absent when its first defined token does not resolve to a family list.
	 */
	public static function values(): array {
		if ( null !== self::$values ) {
			return self::$values;
		}

		self::$values = array();
		$post         = Beaver_Light_Design_System::default_post();

		if ( null === $post ) {
			return self::$values;
		}

		$tokens = Beaver_Light_Design_Tokens::tokens( $post );
		$roles  = Beaver_Light_Styles::roles();

		foreach ( self::ROLES as $role ) {
			$family = self::resolve_family( $roles[ $role ][0], $tokens );

			if ( null !== $family ) {
				self::$values[ $role ] = $family;
			}
		}

		return self::$values;
	}

	/**
	 * The first listed token the map defines, resolved, as CSS picks the first defined var().
	 *
	 * @since 0.1.0
	 * @param string[] $names  Token names without the --ds- prefix.
	 * @param array    $tokens Custom property name => stored value.
	 * @return string|null Null when no listed token is defined or the first one is not a family list.
	 */
	private static function resolve_family( array $names, array $tokens ): ?string {
		foreach ( $names as $name ) {
			if ( ! isset( $tokens[ '--ds-' . $name ] ) ) {
				continue;
			}

			$value = Beaver_Light_Design_Tokens::value( '--ds-' . $name, $tokens );

			return null !== $value && preg_match( self::FAMILY, $value ) ? trim( $value ) : null;
		}

		return null;
	}

	/**
	 * Give the Style Book the body and heading fonts the front end resolves, as theme.json styles.
	 *
	 * Front-end requests are left alone: core prints these styles unlayered, so
	 * there they would beat child theme CSS and reach headings in builder layouts.
	 *
	 * @since 0.1.0
	 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
	 * @return WP_Theme_JSON_Data
	 */
	public static function theme_json_fonts( $theme_json ) {
		$is_front_end = ! is_admin() && ! wp_is_serving_rest_request();

		if ( $is_front_end ) {
			return $theme_json;
		}

		$fallbacks = Beaver_Light_Styles::fallbacks();

		return $theme_json->update_with(
			array(
				'version' => 3,
				'styles'  => array(
					'typography' => array( 'fontFamily' => $fallbacks['font-body'] ),
					'elements'   => array(
						'heading' => array( 'typography' => array( 'fontFamily' => $fallbacks['font-heading'] ) ),
					),
				),
			)
		);
	}
}
