<?php
/**
 * The canonical theme hook map, the single source of truth.
 *
 * Templates fire these positions, BLT_Themer registers them with Beaver
 * Themer's Parts UI, and BLT_Compat_FL maps the legacy fl_* aliases onto them.
 * The names become public API at the first beta.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Canonical hook registry.
 */
final class BLT_Hooks {

	/**
	 * The hook map: group label => [ hook name => human label ].
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public static function map(): array {
		return array(
			__( 'Page', 'beaver-light-theme' )     => array(
				'blt_page_open'  => __( 'Page Open', 'beaver-light-theme' ),
				'blt_page_close' => __( 'Page Close', 'beaver-light-theme' ),
			),
			__( 'Header', 'beaver-light-theme' )   => array(
				'blt_header_before' => __( 'Before Header', 'beaver-light-theme' ),
				'blt_header_after'  => __( 'After Header', 'beaver-light-theme' ),
			),
			__( 'Content', 'beaver-light-theme' )  => array(
				'blt_content_before' => __( 'Before Content', 'beaver-light-theme' ),
				'blt_content_open'   => __( 'Content Open', 'beaver-light-theme' ),
				'blt_content_close'  => __( 'Content Close', 'beaver-light-theme' ),
				'blt_content_after'  => __( 'After Content', 'beaver-light-theme' ),
			),
			__( 'Loop', 'beaver-light-theme' )     => array(
				'blt_loop_before' => __( 'Before Loop', 'beaver-light-theme' ),
				'blt_loop_after'  => __( 'After Loop', 'beaver-light-theme' ),
			),
			__( 'Entry', 'beaver-light-theme' )    => array(
				'blt_entry_before'         => __( 'Before Entry', 'beaver-light-theme' ),
				'blt_entry_meta_open'      => __( 'Entry Meta Open', 'beaver-light-theme' ),
				'blt_entry_meta_close'     => __( 'Entry Meta Close', 'beaver-light-theme' ),
				'blt_entry_content_before' => __( 'Before Entry Content', 'beaver-light-theme' ),
				'blt_entry_content_after'  => __( 'After Entry Content', 'beaver-light-theme' ),
				'blt_entry_footer_open'    => __( 'Entry Footer Open', 'beaver-light-theme' ),
				'blt_entry_footer_close'   => __( 'Entry Footer Close', 'beaver-light-theme' ),
				'blt_entry_after'          => __( 'After Entry', 'beaver-light-theme' ),
			),
			__( 'Footer', 'beaver-light-theme' )   => array(
				'blt_footer_before' => __( 'Before Footer', 'beaver-light-theme' ),
				'blt_footer_open'   => __( 'Footer Open', 'beaver-light-theme' ),
				'blt_footer_close'  => __( 'Footer Close', 'beaver-light-theme' ),
				'blt_footer_after'  => __( 'After Footer', 'beaver-light-theme' ),
			),
			__( 'Comments', 'beaver-light-theme' ) => array(
				'blt_comments_open'  => __( 'Comments Open', 'beaver-light-theme' ),
				'blt_comments_close' => __( 'Comments Close', 'beaver-light-theme' ),
			),
		);
	}

	/**
	 * Flat list of every canonical hook name.
	 *
	 * @since 0.1.0
	 * @return string[]
	 */
	public static function names(): array {
		$names = array();

		foreach ( self::map() as $hooks ) {
			$names = array_merge( $names, array_keys( $hooks ) );
		}

		return $names;
	}

	/**
	 * Fire a canonical hook; an unknown name in debug means a template drifted from the map.
	 *
	 * @since 0.1.0
	 * @param string $name Canonical hook name.
	 * @return void
	 */
	public static function fire( string $name ): void {
		if ( WP_DEBUG && ! in_array( $name, self::names(), true ) ) {
			_doing_it_wrong( __METHOD__, esc_html( "Unknown BLT hook: {$name}" ), '0.1.0' );
		}

		do_action( $name ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}
}
