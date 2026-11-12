<?php
/**
 * Read the site default design system's color tokens.
 *
 * Uses only the design system plugin's public token read,
 * DesignSystemPostType::get_structured_data()['tokens'], on the post
 * Beaver_Light_Design_System::default_post() returns; it holds names and values as stored.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * Design system token reader.
 */
final class Beaver_Light_Design_Tokens {

	/**
	 * A color token name; the category prefix is fixed and the rest is free.
	 */
	const COLOR_NAME = '/^--ds-(color-[a-zA-Z0-9-]+)$/D';

	/**
	 * A var() reference, with an optional fallback.
	 */
	const REFERENCE = '/^var\(\s*(--[a-z0-9_-]+)\s*(?:,(.*))?\)$/isD';

	/**
	 * How many references deep a value may resolve.
	 */
	const MAX_DEPTH = 8;

	/**
	 * How many resolve steps one token may take, since nested fallbacks fan out.
	 */
	const MAX_STEPS = 64;

	/**
	 * Longer values are not colors, and nested fallbacks cost memory in proportion to length squared.
	 */
	const MAX_LENGTH = 512;

	/**
	 * A design system's stored token map.
	 *
	 * @since 0.1.0
	 * @param WP_Post $post Design system post.
	 * @return array Custom property name => stored value.
	 */
	public static function tokens( WP_Post $post ): array {
		$data = call_user_func( array( Beaver_Light_Design_System::POST_TYPE, 'get_structured_data' ), $post );

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

			$budget              = self::MAX_STEPS;
			$resolved            = self::resolve( $value, $tokens, array( $name ), $budget );
			$colors[ $match[1] ] = null === $resolved ? null : Beaver_Light_Color::parse( $resolved );
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
	 * @param int      $budget  Resolve steps left for this token.
	 * @return string|null Null for a non-string, an overlong value, a cycle, a missing reference with no fallback, or a spent budget.
	 */
	private static function resolve( $value, array $tokens, array $visited, int &$budget ): ?string {
		if ( ! is_string( $value ) || strlen( $value ) > self::MAX_LENGTH || --$budget < 0 ) {
			return null;
		}

		if ( ! preg_match( self::REFERENCE, trim( $value ), $match ) ) {
			return trim( $value );
		}

		$name     = $match[1];
		$fallback = isset( $match[2] ) && '' !== trim( $match[2] ) ? $match[2] : null;
		$followed = isset( $tokens[ $name ] ) && ! in_array( $name, $visited, true ) && count( $visited ) <= self::MAX_DEPTH;
		$resolved = $followed ? self::resolve( $tokens[ $name ], $tokens, array_merge( $visited, array( $name ) ), $budget ) : null;

		if ( null !== $resolved || null === $fallback ) {
			return $resolved;
		}

		return self::resolve( $fallback, $tokens, $visited, $budget );
	}
}
