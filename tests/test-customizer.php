<?php
/**
 * The Customizer signpost: Beaver_Light_Customizer.
 *
 * @package Beaver_Light
 */

/**
 * One section that links to where the look is set, with no settings.
 */
class Test_Customizer extends Beaver_Light_Test_Case {

	/**
	 * A Customizer manager after the theme's registration ran, as an administrator.
	 *
	 * @return WP_Customize_Manager
	 */
	private function customizer(): WP_Customize_Manager {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$wp_customize = new WP_Customize_Manager();
		Beaver_Light_Customizer::register( $wp_customize );

		return $wp_customize;
	}

	/**
	 * The registration is hooked to the Customizer.
	 */
	public function test_hooked() {
		$this->assertSame( 10, has_action( 'customize_register', array( 'Beaver_Light_Customizer', 'register' ) ) );
	}

	/**
	 * Without Beaver Builder Pro's Global Styles, as on Lite, whose stand-in Global Styles class is loaded here, only the design system is linked.
	 */
	public function test_no_global_styles_link_without_pro() {
		$urls = wp_list_pluck( Beaver_Light_Customizer::links(), 'url' );

		$this->assertTrue( class_exists( 'FLBuilderGlobalStyles' ) );
		$this->assertSame( array( admin_url( 'edit.php?post_type=fl-builder-template&page=fl-design-systems' ) ), $urls );
	}

	/**
	 * With Pro's Global Styles loaded, it is linked to its documentation after the design system's admin page.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_links_global_styles_with_pro() {
		define( 'FL_BUILDER_GLOBAL_STYLES_DIR', __DIR__ . '/' );

		$urls = wp_list_pluck( Beaver_Light_Customizer::links(), 'url' );

		$this->assertSame(
			array(
				admin_url( 'edit.php?post_type=fl-builder-template&page=fl-design-systems' ),
				Beaver_Light_Customizer::GLOBAL_STYLES_DOCS,
			),
			$urls
		);
	}

	/**
	 * The section holds one control that carries the links and no setting, so nothing is saved.
	 */
	public function test_section_has_no_settings() {
		$wp_customize = $this->customizer();
		$control      = $wp_customize->get_control( Beaver_Light_Customizer::SECTION );

		$this->assertNotNull( $wp_customize->get_section( Beaver_Light_Customizer::SECTION ) );
		$this->assertInstanceOf( 'Beaver_Light_Customize_Pointer_Control', $control );
		$this->assertSame( array(), $control->settings );
		$this->assertNull( $wp_customize->get_setting( Beaver_Light_Customizer::SECTION ) );
		$this->assertTrue( $control->check_capabilities() );
	}

	/**
	 * A user who cannot edit theme options does not get the control.
	 */
	public function test_control_needs_theme_options() {
		$control = $this->customizer()->get_control( Beaver_Light_Customizer::SECTION );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$this->assertFalse( $control->check_capabilities() );
	}

	/**
	 * The control prints each link, escaped, and no form input.
	 */
	public function test_control_prints_links() {
		$control = $this->customizer()->get_control( Beaver_Light_Customizer::SECTION );
		$html    = $control->get_content();

		$this->assertStringContainsString( 'href="' . esc_url( Beaver_Light_Customizer::links()[0]['url'] ) . '"', $html );
		$this->assertStringNotContainsString( '<input', $html );
	}
}
