<?php
/**
 * The styling chain: BLT_Styles::bridge_css().
 *
 * @package Beaver_Light_Theme
 */

/**
 * Each role resolves design system token, then Global Styles, then the theme default.
 */
class Test_Styles_Bridge extends BLT_Test_Case {

	/**
	 * With no sources, every role falls back to its theme default inside its token chain.
	 */
	public function test_no_sources_prints_defaults() {
		$css = BLT_Styles::bridge_css();

		$this->assertStringStartsWith( ':root{', $css );
		$this->assertStringEndsWith( '}', $css );
		$this->assertStringContainsString( '--blt-surface: var(--ds-color-surface, var(--ds-color-ground, #ffffff));', $css );
		$this->assertStringContainsString( '--blt-text: var(--ds-color-text, #1f2328);', $css );
		$this->assertStringContainsString( '--blt-radius: var(--ds-radius-md, var(--ds-radius-sm, 4px));', $css );
		$this->assertStringContainsString( '--blt-site-width: 1100px;', $css );
		$this->assertStringContainsString( '--blt-gutter: 20px;', $css );
	}

	/**
	 * Every role gets exactly one declaration, plus the two widths.
	 */
	public function test_one_declaration_per_role() {
		$css = BLT_Styles::bridge_css();

		foreach ( array_keys( BLT_Styles::roles() ) as $role ) {
			$this->assertSame( 1, substr_count( $css, "--blt-{$role}:" ), $role );
		}

		$this->assertSame( count( BLT_Styles::roles() ) + 2, substr_count( $css, '--blt-' ) );
	}

	/**
	 * Design system tokens nest in listed order, so the first token a kit defines wins.
	 */
	public function test_design_system_tokens_nest_in_order() {
		$css = BLT_Styles::bridge_css();

		$this->assertStringContainsString(
			'--blt-heading: var(--ds-color-heading, var(--ds-color-ink-deep, var(--ds-color-ink, var(--ds-color-text, #1f2328))));',
			$css
		);
	}

	/**
	 * Global Styles colors replace the default but stay inside the token chain.
	 */
	public function test_global_styles_colors_sit_under_tokens() {
		FLBuilderGlobalStyles::$settings = (object) array(
			'text_color' => 'ff0000',
			'h_color'    => 'rgb(1, 2, 3)',
		);

		$css = BLT_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-text: var(--ds-color-text, #ff0000);', $css );
		$this->assertStringContainsString(
			'--blt-heading: var(--ds-color-heading, var(--ds-color-ink-deep, var(--ds-color-ink, var(--ds-color-text, rgb(1, 2, 3)))));',
			$css
		);
		$this->assertStringContainsString( '--blt-muted: var(--ds-color-text-muted, var(--ds-color-muted, #59606b));', $css );
	}

	/**
	 * The link color feeds both link and primary, and the button background overrides primary.
	 */
	public function test_button_background_wins_primary_over_link_color() {
		FLBuilderGlobalStyles::$settings = (object) array(
			'link_color'        => '0000ff',
			'button_background' => '00ff00',
		);

		$css = BLT_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-link: var(--ds-color-primary, var(--ds-color-accent, #0000ff));', $css );
		$this->assertStringContainsString( '--blt-primary: var(--ds-color-primary, var(--ds-color-accent, #00ff00));', $css );
	}

	/**
	 * Global Styles typography fills the font roles; a Default family and an italic weight suffix are handled.
	 */
	public function test_global_styles_typography() {
		FLBuilderGlobalStyles::$settings = (object) array(
			'text_typography' => array(
				'font_family' => 'Open Sans',
				'font_size'   => array(
					'length' => '18',
					'unit'   => 'px',
				),
			),
			'h_typography'    => array(
				'font_family' => 'Default',
				'font_weight' => '600i',
			),
		);

		$css = BLT_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-font-body: var(--ds-font-body, "Open Sans", sans-serif);', $css );
		$this->assertStringContainsString( '--blt-font-size: var(--ds-text-base, 18px);', $css );
		$this->assertStringContainsString( '--blt-font-heading: var(--ds-font-heading, var(--ds-font-display, inherit));', $css );
		$this->assertStringContainsString( '--blt-heading-weight: var(--ds-weight-bold, var(--ds-weight-semibold, 600));', $css );
	}

	/**
	 * The width properties follow the builder's global row settings.
	 */
	public function test_widths_follow_builder_settings() {
		FLBuilderModel::$global_settings = (object) array(
			'row_width'           => '1280',
			'row_padding_left'    => '24',
			'module_margins_left' => '16',
		);

		$css = BLT_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-site-width: 1280px;', $css );
		$this->assertStringContainsString( '--blt-gutter: 40px;', $css );
	}
}
