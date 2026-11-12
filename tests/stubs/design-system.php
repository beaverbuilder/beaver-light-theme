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
	 * Design system posts by the id the fl_ds_site_default option stores.
	 *
	 * @var \WP_Post[]
	 */
	public static $posts = array();

	/**
	 * What get_structured_data() returns as its tokens.
	 *
	 * @var array
	 */
	public static $tokens = array();

	/**
	 * The site default design system post: the option resolved to a post, null when the option is unset or its post is gone.
	 *
	 * @return \WP_Post|null
	 */
	public static function get_default(): ?\WP_Post {
		$id = get_option( 'fl_ds_site_default' );

		return is_string( $id ) ? ( self::$posts[ $id ] ?? null ) : null;
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
