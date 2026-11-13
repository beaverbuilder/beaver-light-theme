<?php
/**
 * The styling chain: Beaver_Light_Styles::bridge_css().
 *
 * @package Beaver_Light
 */

use FL\DesignSystem\DesignSystem\DesignSystemPostType;

/**
 * Each role resolves design system token, then Global Styles, then the theme default.
 */
class Test_Styles_Bridge extends Beaver_Light_Test_Case {

	/**
	 * With no sources, every role falls back to its theme default inside its token chain.
	 */
	public function test_no_sources_prints_defaults() {
		$css = Beaver_Light_Styles::bridge_css();

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
		$css = Beaver_Light_Styles::bridge_css();

		foreach ( array_keys( Beaver_Light_Styles::roles() ) as $role ) {
			$this->assertSame( 1, substr_count( $css, "--blt-{$role}:" ), $role );
		}

		$this->assertSame( count( Beaver_Light_Styles::roles() ) + 2, substr_count( $css, '--blt-' ) );
	}

	/**
	 * Design system tokens nest in listed order, so the first token a kit defines wins.
	 */
	public function test_design_system_tokens_nest_in_order() {
		$css = Beaver_Light_Styles::bridge_css();

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

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-text: var(--ds-color-text, #ff0000);', $css );
		$this->assertStringContainsString(
			'--blt-heading: var(--ds-color-heading, var(--ds-color-ink-deep, var(--ds-color-ink, var(--ds-color-text, rgb(1, 2, 3)))));',
			$css
		);
		$this->assertStringContainsString( '--blt-muted: var(--ds-color-text-muted, var(--ds-color-muted, #59606b));', $css );
		$this->assertStringContainsString( '--blt-line: var(--ds-color-border, var(--ds-color-line, #e3e5e8));', $css );
	}

	/**
	 * Each Global Styles color field and the roles it feeds.
	 *
	 * @return array
	 */
	public function data_color_fields() {
		return array(
			'text_color'              => array( 'text_color', array( 'text' ) ),
			'h_color'                 => array( 'h_color', array( 'heading' ) ),
			'link_color'              => array( 'link_color', array( 'link', 'primary' ) ),
			'link_hover_color'        => array( 'link_hover_color', array( 'link-hover' ) ),
			'button_color'            => array( 'button_color', array( 'on-primary' ) ),
			'button_background'       => array( 'button_background', array( 'primary' ) ),
			'button_hover_background' => array( 'button_hover_background', array( 'primary-hover' ) ),
		);
	}

	/**
	 * A color field lands, under its tokens, in exactly the roles it feeds.
	 *
	 * @dataProvider data_color_fields
	 *
	 * @param string   $field Global Styles field.
	 * @param string[] $roles Roles it should feed.
	 */
	public function test_color_field_feeds_its_roles( string $field, array $roles ) {
		FLBuilderGlobalStyles::$settings = (object) array( $field => 'abcdef' );

		$css = Beaver_Light_Styles::bridge_css();

		foreach ( $roles as $role ) {
			$this->assertMatchesRegularExpression( "/--blt-{$role}: (var\\(--ds-[a-z-]+, )+#abcdef\\)+;/", $css, $role );
		}

		$this->assertSame( count( $roles ), substr_count( $css, '#abcdef' ) );
	}

	/**
	 * The button background overrides the link color for primary, and link keeps the link color.
	 */
	public function test_button_background_wins_primary_over_link_color() {
		FLBuilderGlobalStyles::$settings = (object) array(
			'link_color'        => '0000ff',
			'button_background' => '00ff00',
		);

		$css = Beaver_Light_Styles::bridge_css();

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

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-font-body: var(--ds-font-body, "Open Sans", sans-serif);', $css );
		$this->assertStringContainsString( '--blt-font-size: var(--ds-text-base, 18px);', $css );
		$this->assertStringContainsString( '--blt-font-heading: var(--ds-font-heading, var(--ds-font-display, inherit));', $css );
		$this->assertStringContainsString( '--blt-heading-weight: var(--ds-weight-bold, var(--ds-weight-semibold, 600));', $css );
	}

	/**
	 * A heading family fills font-heading, a one-word family stays unquoted, a default weight is skipped, and a unitless size gets px.
	 */
	public function test_global_styles_typography_edge_cases() {
		FLBuilderGlobalStyles::$settings = (object) array(
			'text_typography' => array( 'font_size' => array( 'length' => '17' ) ),
			'h_typography'    => array(
				'font_family' => 'Georgia',
				'font_weight' => 'default',
			),
		);

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-font-heading: var(--ds-font-heading, var(--ds-font-display, Georgia, sans-serif));', $css );
		$this->assertStringContainsString( '--blt-heading-weight: var(--ds-weight-bold, var(--ds-weight-semibold, 700));', $css );
		$this->assertStringContainsString( '--blt-font-size: var(--ds-text-base, 17px);', $css );
		$this->assertStringContainsString( '--blt-font-body: var(--ds-font-body, system-ui,', $css );
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

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-site-width: 1280px;', $css );
		$this->assertStringContainsString( '--blt-gutter: 40px;', $css );
	}

	/**
	 * An inferred token lands after the listed names and before the fallback; name-matched roles are untouched.
	 */
	public function test_inferred_token_joins_the_chain() {
		self::set_default_design_system( self::factory()->post->create_and_get() );
		DesignSystemPostType::$tokens = array(
			'--ds-color-text'  => '#1e2328',
			'--ds-color-paper' => '#ffffff',
		);

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-surface: var(--ds-color-surface, var(--ds-color-ground, var(--ds-color-paper, #ffffff)));', $css );
		$this->assertStringContainsString( '--blt-text: var(--ds-color-text, #1f2328);', $css );
		$this->assertStringContainsString( '--blt-on-primary: var(--ds-color-on-primary, var(--ds-color-on-accent, #ffffff));', $css );
	}

	/**
	 * A known muted name too faint on the surface leaves the chain, so muted falls through to the theme default.
	 */
	public function test_rejected_muted_name_leaves_the_chain() {
		self::set_default_design_system( self::factory()->post->create_and_get() );
		DesignSystemPostType::$tokens = array(
			'--ds-color-surface' => '#ffffff',
			'--ds-color-text'    => '#1f2328',
			'--ds-color-muted'   => '#e0e0e0',
		);

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-muted: var(--ds-color-text-muted, #59606b);', $css );
		$this->assertStringContainsString( '--blt-text: var(--ds-color-text, #1f2328);', $css );
	}

	/**
	 * A rejected muted name gives way to a token picked by value, after the remaining listed name.
	 */
	public function test_rejected_muted_name_gives_way_to_inferred() {
		self::set_default_design_system( self::factory()->post->create_and_get() );
		DesignSystemPostType::$tokens = array(
			'--ds-color-surface' => '#ffffff',
			'--ds-color-text'    => '#1f2328',
			'--ds-color-muted'   => '#e0e0e0',
			'--ds-color-slate'   => '#59606b',
		);

		$this->assertStringContainsString( '--blt-muted: var(--ds-color-text-muted, var(--ds-color-slate, #59606b));', Beaver_Light_Styles::bridge_css() );
	}

	/**
	 * Rejected names stored for the current revision are applied without a fresh pass.
	 */
	public function test_stored_rejection_is_applied() {
		$post = self::factory()->post->create_and_get();
		self::set_default_design_system( $post );
		update_option(
			'beaver_light_color_roles',
			array(
				'key'      => Beaver_Light_Color_Inference::cache_key( $post ),
				'roles'    => array(),
				'rejected' => array( 'color-text-muted' ),
			)
		);

		$this->assertStringContainsString( '--blt-muted: var(--ds-color-muted, #59606b);', Beaver_Light_Styles::bridge_css() );
	}

	/**
	 * A stored role name that is not a plain token name is never printed, and a malformed rejected name removes nothing.
	 */
	public function test_tampered_cache_is_not_printed() {
		$post = self::factory()->post->create_and_get();
		self::set_default_design_system( $post );
		update_option(
			'beaver_light_color_roles',
			array(
				'key'   => Beaver_Light_Color_Inference::cache_key( $post ),
				'roles' => array(
					'surface' => 'color-x, red); } body { color: red',
					'text'    => array( 'color-x' ),
					'muted'   => "color-x\n",
				),
				'rejected' => array( array( 'color-muted' ), 'color-border' ),
			)
		);

		$css = Beaver_Light_Styles::bridge_css();

		$this->assertStringContainsString( '--blt-surface: var(--ds-color-surface, var(--ds-color-ground, #ffffff));', $css );
		$this->assertStringContainsString( '--blt-text: var(--ds-color-text, #1f2328);', $css );
		$this->assertStringContainsString( '--blt-muted: var(--ds-color-text-muted, var(--ds-color-muted, #59606b));', $css );
	}
}
