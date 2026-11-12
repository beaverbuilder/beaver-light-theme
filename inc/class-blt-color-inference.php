<?php
/**
 * Hybrid color role matching for the site default design system.
 *
 * Known token names are tried first, in CSS, by BLT_Styles::roles(). For each
 * color role none of them matches, this picks a token from the values and
 * returns its name, so the bridge stays a pure var() chain and scoped
 * overrides still apply. The result is cached per design system revision.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inferred color roles, cached.
 */
final class BLT_Color_Inference {

	/**
	 * Bump when the rules change, so cached results are recomputed.
	 */
	const VERSION = 1;

	/**
	 * Option holding the cache key and the inferred roles.
	 */
	const OPTION = 'blt_color_roles';

	/**
	 * Request cache.
	 *
	 * @var array|null
	 */
	private static $roles = null;

	/**
	 * Token names inferred for the color roles known names leave unmatched.
	 *
	 * @since 0.1.0
	 * @return array Role => token name without the --ds- prefix; empty with no site default.
	 */
	public static function roles(): array {
		if ( null !== self::$roles ) {
			return self::$roles;
		}

		$post = BLT_Design_Tokens::default_post();

		if ( null === $post ) {
			self::$roles = array();
			return self::$roles;
		}

		$key    = sprintf( '%d:%s:%d', $post->ID, $post->post_modified_gmt, self::VERSION );
		$stored = get_option( self::OPTION );

		if ( is_array( $stored ) && ( $stored['key'] ?? '' ) === $key && is_array( $stored['roles'] ?? null ) ) {
			self::$roles = $stored['roles'];
			return self::$roles;
		}

		self::$roles = self::infer( BLT_Design_Tokens::tokens( $post ) );
		update_option(
			self::OPTION,
			array(
				'key'   => $key,
				'roles' => self::$roles,
			)
		);

		return self::$roles;
	}

	/**
	 * Infer the unmatched color roles from a token map.
	 *
	 * @since 0.1.0
	 * @param array $tokens Custom property name => stored value.
	 * @return array Role => token name, for inferred roles only.
	 */
	public static function infer( array $tokens ): array {
		$colors = BLT_Design_Tokens::colors( $tokens );
		$rules  = new BLT_Color_Rules( array_filter( $colors ), self::seeds( array_keys( $colors ) ) );

		return $rules->infer();
	}

	/**
	 * The color roles a known name matches: the first listed name present, as the CSS chain resolves it.
	 *
	 * @since 0.1.0
	 * @param string[] $names Color token names present, without the --ds- prefix.
	 * @return array Role => token name.
	 */
	public static function seeds( array $names ): array {
		$seeds = array();

		foreach ( BLT_Styles::roles() as $role => $def ) {
			$matched = array_values( array_intersect( $def[0], $names ) );

			if ( isset( $matched[0] ) && 0 === strpos( $matched[0], 'color-' ) ) {
				$seeds[ $role ] = $matched[0];
			}
		}

		return $seeds;
	}
}
