<?php
/**
 * Legacy fl_* hook aliases, so Themer Parts saved against bb-theme keep rendering.
 *
 * The set is frozen at the names Themer's bb-theme shim registers; new
 * positions get canonical blt_* names only.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legacy fl_* alias layer.
 */
final class BLT_Compat_FL {

	/**
	 * Frozen alias map: canonical blt_* hook => legacy fl_* alias.
	 *
	 * @var array
	 */
	const MAP = array(
		'blt_page_open'            => 'fl_page_open',
		'blt_page_close'           => 'fl_page_close',
		'blt_header_before'        => 'fl_before_header',
		'blt_header_after'         => 'fl_after_header',
		'blt_content_before'       => 'fl_before_content',
		'blt_content_open'         => 'fl_content_open',
		'blt_content_close'        => 'fl_content_close',
		'blt_content_after'        => 'fl_after_content',
		'blt_footer_before'        => 'fl_before_footer',
		'blt_footer_open'          => 'fl_before_footer_widgets',
		'blt_footer_close'         => 'fl_after_footer_widgets',
		'blt_footer_after'         => 'fl_after_footer',
		'blt_entry_before'         => 'fl_before_post',
		'blt_entry_after'          => 'fl_after_post',
		'blt_entry_content_before' => 'fl_before_post_content',
		'blt_entry_content_after'  => 'fl_after_post_content',
		'blt_entry_meta_open'      => 'fl_post_top_meta_open',
		'blt_entry_meta_close'     => 'fl_post_top_meta_close',
		'blt_entry_footer_open'    => 'fl_post_bottom_meta_open',
		'blt_entry_footer_close'   => 'fl_post_bottom_meta_close',
		'blt_comments_open'        => 'fl_comments_open',
		'blt_comments_close'       => 'fl_comments_close',
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
