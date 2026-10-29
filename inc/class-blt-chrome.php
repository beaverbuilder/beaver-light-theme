<?php
/**
 * The native header and footer, and the page-shape helpers templates share.
 *
 * Both render through removable actions (blt_header, blt_footer) so Themer
 * layouts can replace them; see BLT_Themer::setup_headers_and_footers().
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header, footer and page-shape helpers.
 */
final class BLT_Chrome {

	/**
	 * Wire the native header/footer renderers and body classes.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		add_action( 'blt_header', array( __CLASS__, 'render_header' ) );
		add_action( 'blt_footer', array( __CLASS__, 'render_footer' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * Whether this request renders a header and footer at all.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function show_header_footer(): bool {
		$is_bare_template = is_page_template( 'tpl-no-header-footer.php' );

		/**
		 * Filter whether the theme outputs its header and footer slots.
		 *
		 * @param bool $show Default true, false on the No Header/Footer template.
		 */
		return (bool) apply_filters( 'blt_header_footer', ! $is_bare_template );
	}

	/**
	 * Whether the current singular view is laid out by Beaver Builder.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public static function is_builder_page(): bool {
		$has_builder = class_exists( 'FLBuilderModel' );

		return $has_builder && is_singular() && FLBuilderModel::is_builder_enabled();
	}

	/**
	 * Render the native header part.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function render_header(): void {
		get_template_part( 'parts/header' );
	}

	/**
	 * Render the native footer part inside the footer open/close positions.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function render_footer(): void {
		BLT_Hooks::fire( 'blt_footer_open' );
		get_template_part( 'parts/footer' );
		BLT_Hooks::fire( 'blt_footer_close' );
	}

	/**
	 * The footer copyright line: the year and the site name, filterable.
	 *
	 * @since 0.1.0
	 * @return string Plain text; escaped at the render site.
	 */
	public static function copyright(): string {
		$default = sprintf(
			/* translators: 1: current year, 2: site name. */
			__( '© %1$s %2$s', 'beaver-light-theme' ),
			wp_date( 'Y' ),
			get_bloginfo( 'name' )
		);

		/**
		 * Filter the footer copyright text.
		 *
		 * @param string $text Default "© {year} {site name}".
		 */
		return (string) apply_filters( 'blt_footer_copyright', $default );
	}

	/**
	 * Add page-shape classes CSS keys off.
	 *
	 * @since 0.1.0
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function body_class( array $classes ): array {
		if ( self::is_builder_page() ) {
			$classes[] = 'blt-builder-page';
		}

		if ( ! self::show_header_footer() ) {
			$classes[] = 'blt-no-header-footer';
		}

		return $classes;
	}
}
