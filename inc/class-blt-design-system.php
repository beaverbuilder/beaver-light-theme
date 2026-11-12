<?php
/**
 * Tier 1 of the styling chain: the Beaver Builder AI design system.
 *
 * The theme only uses the plugin's public seams: the site default read
 * DesignSystemPostType::get_default(), the site-wide loading filter and the
 * .fl-ds-page scope class its typography floor targets.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Design system hookup.
 */
final class BLT_Design_System {

	/**
	 * The design system plugin's post type class.
	 */
	const POST_TYPE = '\FL\DesignSystem\DesignSystem\DesignSystemPostType';

	/**
	 * Wire the site-wide loading answer.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'fl_design_system_load_default_site_wide', array( __CLASS__, 'load_site_wide' ) );
	}

	/**
	 * Whether a site default design system exists.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function has_site_default(): bool {
		return null !== self::default_post();
	}

	/**
	 * The site default design system post, if one exists and its plugin is loaded; gates on the post, not the option, because the option can outlive its post.
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
	 * Turn site-wide loading on when a default exists, so blog, archive and 404 pages get its tokens.
	 *
	 * This reverses bb-platform's opt-in default for BLT sites, as agreed with
	 * that team; a later-priority filter still overrides it.
	 *
	 * @since 0.1.0
	 * @param bool $enabled Current answer.
	 * @return bool
	 */
	public static function load_site_wide( $enabled ): bool {
		return self::has_site_default() ? true : (bool) $enabled;
	}

	/**
	 * The scope class for the content wrapper; the design system's typography floor is scoped to it.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function page_class(): string {
		return 'fl-ds-page';
	}
}
