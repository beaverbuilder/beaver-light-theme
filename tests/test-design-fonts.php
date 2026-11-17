<?php
/**
 * Design system font families and the Style Book feed: Beaver_Light_Design_Fonts.
 *
 * @package Beaver_Light
 */

use FL\DesignSystem\DesignSystem\DesignSystemPostType;

/**
 * The site default's font tokens resolve in PHP, sit under the bridge's tokens, and reach theme.json only off the front end.
 */
class Test_Design_Fonts extends Beaver_Light_Test_Case {

	/**
	 * Leave the request as the front end.
	 *
	 * @return void
	 */
	public function tear_down() {
		set_current_screen( 'front' );
		parent::tear_down();
	}

	/**
	 * Make a site default design system with these tokens.
	 *
	 * @param array $tokens Custom property name => stored value.
	 * @return void
	 */
	private function with_tokens( array $tokens ): void {
		DesignSystemPostType::$tokens = $tokens;
		self::set_default_design_system( self::factory()->post->create_and_get() );
	}

	/**
	 * Theme JSON styles after the feed, from an empty theme.json.
	 *
	 * @return array
	 */
	private function fed_styles(): array {
		$data = Beaver_Light_Design_Fonts::theme_json_fonts( new WP_Theme_JSON_Data( array( 'version' => 3 ), 'theme' ) );

		return $data->get_data()['styles'] ?? array();
	}

	/**
	 * With no site default, nothing resolves.
	 */
	public function test_no_site_default_resolves_nothing() {
		$this->assertSame( array(), Beaver_Light_Design_Fonts::values() );
	}

	/**
	 * Body and heading families resolve through var() references, and heading falls to the display token.
	 */
	public function test_resolves_families() {
		$this->with_tokens(
			array(
				'--ds-font-sans'    => '"Inter Tight", system-ui, sans-serif',
				'--ds-font-body'    => 'var(--ds-font-sans)',
				'--ds-font-display' => "'Playfair Display', serif",
			)
		);

		$this->assertSame(
			array(
				'font-body'    => '"Inter Tight", system-ui, sans-serif',
				'font-heading' => "'Playfair Display', serif",
			),
			Beaver_Light_Design_Fonts::values()
		);
	}

	/**
	 * Values that could break out of a declaration are dropped, and an unusable first token is not passed over for a later one.
	 *
	 * @return array
	 */
	public function data_unsafe_values() {
		return array(
			'closing brace'     => array( 'Inter;}body{color:red' ),
			'unbalanced quote'  => array( '"Inter, sans-serif' ),
			'mixed quotes'      => array( '"Inter\', sans-serif' ),
			'style tag'         => array( '</style><script>' ),
			'function'          => array( 'url(x)' ),
			'missing reference' => array( 'var(--ds-font-nope)' ),
			'empty'             => array( '' ),
		);
	}

	/**
	 * An unsafe or unresolvable body value resolves to nothing.
	 *
	 * @dataProvider data_unsafe_values
	 *
	 * @param string $value Stored token value.
	 */
	public function test_unsafe_values_are_dropped( string $value ) {
		$this->with_tokens(
			array(
				'--ds-font-heading' => $value,
				'--ds-font-display' => 'Georgia, serif',
			)
		);

		$this->assertSame( array(), Beaver_Light_Design_Fonts::values() );
	}

	/**
	 * The design system family sits under its token in the bridge, ahead of Global Styles.
	 */
	public function test_bridge_falls_back_to_design_system_family() {
		FLBuilderGlobalStyles::$settings = (object) array( 'text_typography' => array( 'font_family' => 'Open Sans' ) );
		$this->with_tokens( array( '--ds-font-body' => 'Inter, sans-serif' ) );

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-font-body: var(--ds-font-body, Inter, sans-serif);', $css );
		$this->assertStringContainsString( '--blt-font-heading: var(--ds-font-heading, var(--ds-font-display, inherit));', $css );
	}

	/**
	 * The front end gets no theme.json fonts, since core prints them unlayered.
	 */
	public function test_front_end_gets_no_theme_json_fonts() {
		$this->with_tokens( array( '--ds-font-body' => 'Inter, sans-serif' ) );

		$this->assertSame( array(), $this->fed_styles() );
	}

	/**
	 * In the admin, theme.json carries the design system families.
	 */
	public function test_admin_gets_design_system_fonts() {
		set_current_screen( 'site-editor' );
		$this->with_tokens(
			array(
				'--ds-font-body'    => 'Inter, sans-serif',
				'--ds-font-heading' => '"Playfair Display", serif',
			)
		);

		$styles = $this->fed_styles();

		$this->assertSame( 'Inter, sans-serif', $styles['typography']['fontFamily'] );
		$this->assertSame( '"Playfair Display", serif', $styles['elements']['heading']['typography']['fontFamily'] );
	}

	/**
	 * Without a design system, theme.json carries Global Styles, then the theme defaults.
	 */
	public function test_admin_falls_back_to_global_styles_and_defaults() {
		set_current_screen( 'site-editor' );
		FLBuilderGlobalStyles::$settings = (object) array( 'h_typography' => array( 'font_family' => 'Lora' ) );

		$styles = $this->fed_styles();

		$this->assertSame( Beaver_Light_Styles::roles()['font-body'][1], $styles['typography']['fontFamily'] );
		$this->assertStringStartsWith( 'Lora, ', $styles['elements']['heading']['typography']['fontFamily'] );
	}
}
