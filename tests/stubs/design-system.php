<?php
/**
 * Stand-in for the design system plugin's public token read.
 *
 * Answers from public static state a test sets, so the suite runs without the
 * plugin. Only the two methods the theme calls exist.
 *
 * @package Beaver_Light_Theme
 */

namespace FL\DesignSystem\DesignSystem;

/**
 * Design system post type stand-in.
 */
class DesignSystemPostType {

	/**
	 * What get_default() returns.
	 *
	 * @var \WP_Post|null
	 */
	public static $default = null;

	/**
	 * What get_structured_data() returns as its tokens.
	 *
	 * @var array
	 */
	public static $tokens = array();

	/**
	 * The site default design system post.
	 *
	 * @return \WP_Post|null
	 */
	public static function get_default(): ?\WP_Post {
		return self::$default;
	}

	/**
	 * A design system's stored data; only the tokens are modeled.
	 *
	 * @param \WP_Post $post Design system post.
	 * @return array
	 */
	public static function get_structured_data( \WP_Post $post ): array {
		return array( 'tokens' => self::$tokens );
	}
}
