<?php
/**
 * Legacy fl_* hook aliases, so Themer Parts saved against bb-theme keep rendering.
 *
 * The set is frozen at the names Themer's bb-theme shim registers; new
 * positions get canonical beaver_light_* names only.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legacy fl_* alias layer.
 */
final class Beaver_Light_Compat_FL {

	/**
	 * Frozen alias map: canonical beaver_light_* hook => legacy fl_* alias.
	 *
	 * @var array
	 */
	const MAP = array(
		'beaver_light_page_open'            => 'fl_page_open',
		'beaver_light_page_close'           => 'fl_page_close',
		'beaver_light_header_before'        => 'fl_before_header',
		'beaver_light_header_after'         => 'fl_after_header',
		'beaver_light_content_before'       => 'fl_before_content',
		'beaver_light_content_open'         => 'fl_content_open',
		'beaver_light_content_close'        => 'fl_content_close',
		'beaver_light_content_after'        => 'fl_after_content',
		'beaver_light_footer_before'        => 'fl_before_footer',
		'beaver_light_footer_open'          => 'fl_before_footer_widgets',
		'beaver_light_footer_close'         => 'fl_after_footer_widgets',
		'beaver_light_footer_after'         => 'fl_after_footer',
		'beaver_light_entry_before'         => 'fl_before_post',
		'beaver_light_entry_after'          => 'fl_after_post',
		'beaver_light_entry_content_before' => 'fl_before_post_content',
		'beaver_light_entry_content_after'  => 'fl_after_post_content',
		'beaver_light_entry_meta_open'      => 'fl_post_top_meta_open',
		'beaver_light_entry_meta_close'     => 'fl_post_top_meta_close',
		'beaver_light_entry_footer_open'    => 'fl_post_bottom_meta_open',
		'beaver_light_entry_footer_close'   => 'fl_post_bottom_meta_close',
		'beaver_light_comments_open'        => 'fl_comments_open',
		'beaver_light_comments_close'       => 'fl_comments_close',
	);

	/**
	 * Fire each alias when its canonical position fires.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		foreach ( self::MAP as $canonical => $alias ) {
			add_action(
				$canonical,
				static function () use ( $alias ) {
					do_action( $alias ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
				}
			);
		}
	}
}
