<?php
/**
 * The styling chain, width parity, and asset loading.
 *
 * Every visual value the theme's CSS uses is a --blt-* property, printed once
 * per request. Each role resolves on its own: a design system token wins by
 * mere presence, then Global Styles, then the theme default. One look, so
 * every change is incremental (see CONTRIBUTING.md).
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Styling chain and assets.
 */
final class BLT_Styles {

	/**
	 * Wire assets and the theme.json width filter.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'wp_theme_json_data_theme', array( __CLASS__, 'theme_json_widths' ) );
		add_filter( 'block_editor_settings_all', array( __CLASS__, 'editor_settings' ) );
	}

	/**
	 * Role definitions: candidate design system tokens, then the theme default.
	 *
	 * Kits name tokens freely within a category (one says --ds-color-primary,
	 * another --ds-color-accent), so each role lists the names seen in real
	 * kits, first match wins.
	 *
	 * @since 0.1.0
	 * @return array role => [ tokens[], default ]
	 */
	public static function roles(): array {
		$system = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

		// Deliberately plain: the defaults only keep an unstyled site from looking broken.
		return array(
			'surface'        => array( array( 'color-surface', 'color-ground' ), '#ffffff' ),
			'text'           => array( array( 'color-text' ), '#1f2328' ),
			'heading'        => array( array( 'color-heading', 'color-ink-deep', 'color-ink', 'color-text' ), '#1f2328' ),
			'muted'          => array( array( 'color-text-muted', 'color-muted' ), '#59606b' ),
			'line'           => array( array( 'color-border', 'color-line' ), '#e3e5e8' ),
			'primary'        => array( array( 'color-primary', 'color-accent' ), '#1f2328' ),
			'primary-hover'  => array( array( 'color-primary-dark', 'color-accent-deep' ), '#000000' ),
			'on-primary'     => array( array( 'color-on-primary', 'color-on-accent' ), '#ffffff' ),
			'link'           => array( array( 'color-primary', 'color-accent' ), '#1f2328' ),
			'link-hover'     => array( array( 'color-primary-dark', 'color-accent-deep' ), '#000000' ),
			'font-body'      => array( array( 'font-body' ), $system ),
			'font-heading'   => array( array( 'font-heading', 'font-display' ), 'inherit' ),
			'font-size'      => array( array( 'text-base' ), '1rem' ),
			'heading-weight' => array( array( 'weight-bold', 'weight-semibold' ), '700' ),
			'radius'         => array( array( 'radius-md', 'radius-sm' ), '4px' ),
		);
	}

	/**
	 * Nest the candidate tokens as var() fallbacks around the final value.
	 *
	 * @since 0.1.0
	 * @param string[] $tokens   Token names without the --ds- prefix.
	 * @param string   $fallback The Global Styles value or theme default.
	 * @return string
	 */
	private static function chain( array $tokens, string $fallback ): string {
		foreach ( array_reverse( $tokens ) as $token ) {
			$fallback = sprintf( 'var(--ds-%s, %s)', $token, $fallback );
		}

		return $fallback;
	}

	/**
	 * The :root block that resolves every role through the chain.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function bridge_css(): string {
		$global_styles = BLT_Global_Styles::values();
		$widths        = self::widths();
		$lines         = array();

		foreach ( self::roles() as $role => $def ) {
			$fallback = $global_styles[ $role ] ?? $def[1];
			$lines[]  = sprintf( '--blt-%s: %s;', $role, self::chain( $def[0], $fallback ) );
		}

		$lines[] = '--blt-site-width: ' . $widths['site'] . ';';
		$lines[] = '--blt-gutter: ' . $widths['gutter'] . ';';

		return ':root{' . implode( '', $lines ) . '}';
	}

	/**
	 * Width parity: the builder's global row width and the inset text sits at inside a row.
	 *
	 * @since 0.1.0
	 * @return array{site: string, gutter: string}
	 */
	public static function widths(): array {
		$widths = array(
			'site'   => '1100px',
			'gutter' => '20px',
		);

		if ( ! class_exists( 'FLBuilderModel' ) ) {
			return $widths;
		}

		$global      = FLBuilderModel::get_global_settings();
		$row_width   = $global->row_width ?? '';
		$padding     = $global->row_padding_left ?? '';
		$pad_unit    = ! empty( $global->row_padding_unit ) ? $global->row_padding_unit : 'px';
		$margin      = $global->module_margins_left ?? '';
		$margin_unit = ! empty( $global->module_margins_unit ) ? $global->module_margins_unit : 'px';

		if ( '' !== (string) $row_width ) {
			$widths['site'] = $row_width . ( ! empty( $global->row_width_unit ) ? $global->row_width_unit : 'px' );
		}

		// Text in a builder row sits at row padding plus module margin; match it.
		if ( '' !== (string) $padding ) {
			$same_unit        = '' !== (string) $margin && $margin_unit === $pad_unit;
			$widths['gutter'] = ( (float) $padding + ( $same_unit ? (float) $margin : 0 ) ) . $pad_unit;
		}

		return $widths;
	}

	/**
	 * Feed the builder's row width to the block editor as the wide size.
	 *
	 * @since 0.1.0
	 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
	 * @return WP_Theme_JSON_Data
	 */
	public static function theme_json_widths( $theme_json ) {
		$widths = self::widths();

		return $theme_json->update_with(
			array(
				'version'  => 3,
				'settings' => array( 'layout' => array( 'wideSize' => $widths['site'] ) ),
			)
		);
	}

	/**
	 * Print the palette and bridge into the block editor so editor.css resolves the same values as the front end.
	 *
	 * The editor scopes these styles to its canvas, turning :root into .editor-styles-wrapper.
	 *
	 * @since 0.1.0
	 * @param array $settings Block editor settings.
	 * @return array
	 */
	public static function editor_settings( array $settings ): array {
		$settings['styles'][] = array( 'css' => BLT_Global_Styles::palette_css() . self::bridge_css() );

		return $settings;
	}

	/**
	 * Enqueue the two stylesheets, the bridge, and the menu script.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function enqueue(): void {
		wp_enqueue_style( 'blt-theme', BLT_URI . '/assets/css/theme.css', array(), BLT_VERSION );
		wp_enqueue_style( 'blt-chrome', BLT_URI . '/assets/css/chrome.css', array( 'blt-theme' ), BLT_VERSION );
		wp_add_inline_style( 'blt-theme', BLT_Global_Styles::palette_css() . self::bridge_css() );

		wp_enqueue_script(
			'blt-navigation',
			BLT_URI . '/assets/js/navigation.js',
			array(),
			BLT_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
