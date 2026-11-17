<?php
/**
 * A Customizer signpost to where the theme's look is set.
 *
 * The theme has no style settings, so someone opening the Customizer to change
 * colors or fonts is pointed to the design system and Global Styles instead.
 * The section holds one display-only control and registers no settings.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customizer signpost section.
 */
final class Beaver_Light_Customizer {

	/**
	 * The section and control id.
	 */
	const SECTION = 'beaver_light_styling';

	/**
	 * The design system plugin's admin page locator class.
	 */
	const DESIGN_SYSTEM_PAGE = '\FL\DesignSystem\Admin\AdminPageLocator';

	/**
	 * Global Styles has no admin screen, so its link goes to the documentation.
	 */
	const GLOBAL_STYLES_DOCS = 'https://docs.wpbeaverbuilder.com/beaver-builder/user-interface/global-styles';

	/**
	 * Wire the Customizer registration.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function init(): void {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
	}

	/**
	 * Add the signpost section, only when at least one place it points to is installed.
	 *
	 * @since 0.1.0
	 * @param WP_Customize_Manager $wp_customize Customizer manager.
	 * @return void
	 */
	public static function register( WP_Customize_Manager $wp_customize ): void {
		$links = self::links();

		if ( empty( $links ) ) {
			return;
		}

		require_once BEAVER_LIGHT_DIR . '/inc/class-beaver-light-customize-pointer-control.php';

		$wp_customize->add_section(
			self::SECTION,
			array(
				'title'    => __( 'Colors and Fonts', 'beaver-light' ),
				'priority' => 40,
			)
		);
		$wp_customize->add_control(
			new Beaver_Light_Customize_Pointer_Control(
				$wp_customize,
				self::SECTION,
				array(
					'section'    => self::SECTION,
					'settings'   => array(),
					'capability' => 'edit_theme_options',
					'links'      => $links,
				)
			)
		);
	}

	/**
	 * The places the site's look is set, for what is installed: each has a label, a URL and where it lives.
	 *
	 * @since 0.1.0
	 * @return array[]
	 */
	public static function links(): array {
		$links = array();

		if ( self::has_design_system_page() ) {
			$links[] = array(
				'label' => __( 'Design Systems', 'beaver-light' ),
				'url'   => (string) call_user_func( array( self::DESIGN_SYSTEM_PAGE, 'page_url' ) ),
				'where' => __( 'Colors, fonts and spacing for the whole site.', 'beaver-light' ),
			);
		}

		// The constant, not the class: Beaver Builder Lite defines a Global Styles stand-in class without the feature.
		if ( defined( 'FL_BUILDER_GLOBAL_STYLES_DIR' ) ) {
			$links[] = array(
				'label' => __( 'Global Styles', 'beaver-light' ),
				'url'   => self::GLOBAL_STYLES_DOCS,
				'where' => __( 'Open Beaver Builder on any page, then choose Tools, Global Styles.', 'beaver-light' ),
			);
		}

		return $links;
	}

	/**
	 * Whether the design system plugin and its admin page locator are loaded.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	private static function has_design_system_page(): bool {
		return class_exists( Beaver_Light_Design_System::POST_TYPE )
			&& method_exists( self::DESIGN_SYSTEM_PAGE, 'page_url' );
	}
}
