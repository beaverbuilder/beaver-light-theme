<?php
/**
 * Read the site default design system's color tokens.
 *
 * Uses only the design system plugin's public token read:
 * DesignSystemPostType::get_default() and get_structured_data()['tokens'],
 * which return names and values as stored.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Design system token reader.
 */
final class BLT_Design_Tokens {

	/**
	 * The design system plugin's post type class.
	 */
	const POST_TYPE = '\FL\DesignSystem\DesignSystem\DesignSystemPostType';

	/**
	 * A color token name; the category prefix is fixed and the rest is free.
	 */
	const COLOR_NAME = '/^--ds-(color-[a-z0-9-]+)$/i';

	/**
	 * A var() reference, with an optional fallback.
	 */
	const REFERENCE = '/^var\(\s*(--[a-z0-9_-]+)\s*(?:,(.*))?\)$/is';

	/**
	 * How many references deep a value may resolve.
	 */
	const MAX_DEPTH = 8;

	/**
	 * The site default design system post, if one exists and its plugin is loaded.
	 *
	 * Gates on the post, not the option, because the option can outlive its post.
	 *
	 * @since 0.1.0
	 * @return WP_Post|null
	 */
	public static function default_post(): ?WP_Post {
		if ( ! class_exists( self::POST_TYPE ) ) {
			return null;
		}

		$post = call_user_func( array( self::POST_TYPE, 'get_default' ) );

		return $post instanceof WP_Post ? $post : null;
	}

	/**
	 * A design system's stored token map.
	 *
	 * @since 0.1.0
	 * @param WP_Post $post Design system post.
	 * @return array Custom property name => stored value.
	 */
	public static function tokens( WP_Post $post ): array {
		$data = call_user_func( array( self::POST_TYPE, 'get_structured_data' ), $post );

		return is_array( $data['tokens'] ?? null ) ? $data['tokens'] : array();
	}

	/**
	 * Every color token, keyed by name without the --ds- prefix, with var() references resolved.
	 *
	 * @since 0.1.0
	 * @param array $tokens Custom property name => stored value.
	 * @return array Name => RGB channels, or null when the value cannot be measured.
	 */
	public static function colors( array $tokens ): array {
		$colors = array();

		foreach ( $tokens as $name => $value ) {
			if ( ! preg_match( self::COLOR_NAME, (string) $name, $match ) ) {
				continue;
			}

			$resolved            = self::resolve( $value, $tokens, array( $name ) );
			$colors[ $match[1] ] = null === $resolved ? null : BLT_Color::parse( $resolved );
		}

		return $colors;
	}

	/**
	 * Follow var() references through the token map to a final value.
	 *
	 * @since 0.1.0
	 * @param mixed    $value   Stored value.
	 * @param array    $tokens  Custom property name => stored value.
	 * @param string[] $visited Names already followed, against cycles.
	 * @return string|null Null for a non-string, a cycle, or a missing reference with no fallback.
	 */
	private static function resolve( $value, array $tokens, array $visited ): ?string {
		if ( ! is_string( $value ) ) {
			return null;
		}

		if ( ! preg_match( self::REFERENCE, trim( $value ), $match ) ) {
			return trim( $value );
		}

		$name     = $match[1];
		$fallback = isset( $match[2] ) && '' !== trim( $match[2] ) ? $match[2] : null;
		$followed = isset( $tokens[ $name ] ) && ! in_array( $name, $visited, true ) && count( $visited ) <= self::MAX_DEPTH;
		$resolved = $followed ? self::resolve( $tokens[ $name ], $tokens, array_merge( $visited, array( $name ) ) ) : null;

		if ( null !== $resolved || null === $fallback ) {
			return $resolved;
		}

		return self::resolve( $fallback, $tokens, $visited );
	}
}
