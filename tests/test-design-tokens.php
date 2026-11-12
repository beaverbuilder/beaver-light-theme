<?php
/**
 * Reading the site default's color tokens: BLT_Design_Tokens.
 *
 * @package Beaver_Light_Theme
 */

use FL\DesignSystem\DesignSystem\DesignSystemPostType;

/**
 * Only --ds-color-* tokens are read, with var() references resolved through the map.
 */
class Test_Design_Tokens extends BLT_Test_Case {

	/**
	 * A var() chain resolves, and a reference's fallback is used when its target is missing.
	 */
	public function test_resolves_references() {
		$colors = BLT_Design_Tokens::colors(
			array(
				'--ds-color-base'   => '#ffffff',
				'--ds-color-alias'  => 'var(--ds-color-base)',
				'--ds-color-chain'  => 'var( --ds-color-alias )',
				'--ds-color-backup' => 'var(--ds-color-nope, var(--ds-color-base))',
				'--ds-color-plain'  => 'var(--ds-color-nope, #000)',
			)
		);

		$white = array( 255.0, 255.0, 255.0 );
		$this->assertSame( $white, $colors['color-alias'] );
		$this->assertSame( $white, $colors['color-chain'] );
		$this->assertSame( $white, $colors['color-backup'] );
		$this->assertSame( array( 0.0, 0.0, 0.0 ), $colors['color-plain'] );
	}

	/**
	 * A cycle, a missing reference with no fallback, and an unparseable value are kept by name but cannot be measured.
	 */
	public function test_unmeasurable_tokens_are_null() {
		$colors = BLT_Design_Tokens::colors(
			array(
				'--ds-color-a'       => 'var(--ds-color-b)',
				'--ds-color-b'       => 'var(--ds-color-a)',
				'--ds-color-self'    => 'var(--ds-color-self)',
				'--ds-color-missing' => 'var(--ds-color-nope)',
				'--ds-color-wide'    => 'oklch(70% 0.1 250)',
				'--ds-color-array'   => array( '#fff' ),
			)
		);

		$this->assertSame( array( 'color-a', 'color-b', 'color-self', 'color-missing', 'color-wide', 'color-array' ), array_keys( $colors ) );
		$this->assertSame( array(), array_filter( $colors ) );
	}

	/**
	 * A reference deeper than the cap is dropped.
	 */
	public function test_depth_is_capped() {
		$tokens = array( '--ds-color-0' => '#000000' );
		for ( $i = 1; $i <= 12; $i++ ) {
			$tokens[ "--ds-color-{$i}" ] = 'var(--ds-color-' . ( $i - 1 ) . ')';
		}

		$colors = BLT_Design_Tokens::colors( $tokens );

		$this->assertNotNull( $colors['color-8'] );
		$this->assertNull( $colors['color-12'] );
	}

	/**
	 * Non-color tokens are ignored, though a color may reference one.
	 */
	public function test_ignores_other_categories() {
		$colors = BLT_Design_Tokens::colors(
			array(
				'--ds-font-body'      => 'Inter, sans-serif',
				'--ds-system-ink'     => '#111111',
				'--ds-color-'         => '#222222',
				'--ds-color-bad name' => '#333333',
				'--ds-color-ink'      => 'var(--ds-system-ink)',
			)
		);

		$this->assertSame( array( 'color-ink' => array( 17.0, 17.0, 17.0 ) ), $colors );
	}

	/**
	 * With no default post, even when the option is still set, there is nothing to read.
	 */
	public function test_no_default_post() {
		update_option( 'fl_ds_site_default', 'a-uuid-whose-post-is-gone' );

		$this->assertNull( BLT_Design_Tokens::default_post() );
	}

	/**
	 * The default post's stored tokens are read through the public call.
	 */
	public function test_reads_default_tokens() {
		DesignSystemPostType::$default = self::factory()->post->create_and_get();
		DesignSystemPostType::$tokens  = array( '--ds-color-text' => '#111' );

		$post = BLT_Design_Tokens::default_post();

		$this->assertSame( DesignSystemPostType::$default, $post );
		$this->assertSame( array( '--ds-color-text' => '#111' ), BLT_Design_Tokens::tokens( $post ) );
	}
}
