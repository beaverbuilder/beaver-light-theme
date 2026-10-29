<?php
/**
 * Tier 1 of the styling chain: the Beaver Builder AI design system.
 *
 * The theme only uses the plugin's public seams: the FL_DESIGN_SYSTEM_FILE
 * constant, the fl_ds_site_default option, the site-wide loading filter and
 * the .fl-ds-page scope class its typography floor targets.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Design system hookup.
 */
final class BLT_Design_System {

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
		return defined( 'FL_DESIGN_SYSTEM_FILE' ) && (bool) get_option( 'fl_ds_site_default' );
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
