<?php
/**
 * The canonical theme hook map, the single source of truth.
 *
 * Templates fire these positions, Beaver_Light_Themer registers them with Beaver
 * Themer's Parts UI, and Beaver_Light_Compat_FL maps the legacy fl_* aliases onto them.
 * The names become public API at the first beta.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * Canonical hook registry.
 */
final class Beaver_Light_Hooks {

	/**
	 * The hook map: group label => [ hook name => human label ].
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public static function map(): array {
		return array(
			__( 'Page', 'beaver-light' )     => array(
				'beaver_light_page_open'  => __( 'Page Open', 'beaver-light' ),
				'beaver_light_page_close' => __( 'Page Close', 'beaver-light' ),
			),
			__( 'Header', 'beaver-light' )   => array(
				'beaver_light_header_before' => __( 'Before Header', 'beaver-light' ),
				'beaver_light_header_after'  => __( 'After Header', 'beaver-light' ),
			),
			__( 'Content', 'beaver-light' )  => array(
				'beaver_light_content_before' => __( 'Before Content', 'beaver-light' ),
				'beaver_light_content_open'   => __( 'Content Open', 'beaver-light' ),
				'beaver_light_content_close'  => __( 'Content Close', 'beaver-light' ),
				'beaver_light_content_after'  => __( 'After Content', 'beaver-light' ),
			),
			__( 'Loop', 'beaver-light' )     => array(
				'beaver_light_loop_before' => __( 'Before Loop', 'beaver-light' ),
				'beaver_light_loop_after'  => __( 'After Loop', 'beaver-light' ),
			),
			__( 'Entry', 'beaver-light' )    => array(
				'beaver_light_entry_before'         => __( 'Before Entry', 'beaver-light' ),
				'beaver_light_entry_meta_open'      => __( 'Entry Meta Open', 'beaver-light' ),
				'beaver_light_entry_meta_close'     => __( 'Entry Meta Close', 'beaver-light' ),
				'beaver_light_entry_content_before' => __( 'Before Entry Content', 'beaver-light' ),
				'beaver_light_entry_content_after'  => __( 'After Entry Content', 'beaver-light' ),
				'beaver_light_entry_footer_open'    => __( 'Entry Footer Open', 'beaver-light' ),
				'beaver_light_entry_footer_close'   => __( 'Entry Footer Close', 'beaver-light' ),
				'beaver_light_entry_after'          => __( 'After Entry', 'beaver-light' ),
			),
			__( 'Footer', 'beaver-light' )   => array(
				'beaver_light_footer_before' => __( 'Before Footer', 'beaver-light' ),
				'beaver_light_footer_open'   => __( 'Footer Open', 'beaver-light' ),
				'beaver_light_footer_close'  => __( 'Footer Close', 'beaver-light' ),
				'beaver_light_footer_after'  => __( 'After Footer', 'beaver-light' ),
			),
			__( 'Comments', 'beaver-light' ) => array(
				'beaver_light_comments_open'  => __( 'Comments Open', 'beaver-light' ),
				'beaver_light_comments_close' => __( 'Comments Close', 'beaver-light' ),
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
