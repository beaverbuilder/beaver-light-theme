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
		$this->assertNull( $colors['color-9'] );
		$this->assertNull( $colors['color-12'] );
	}

	/**
	 * Nested fallbacks that fan out stop at the step budget: unbounded, color-0 reaches its last fallback after tens of thousands of steps, so null proves the budget stopped it.
	 */
	public function test_fan_out_is_bounded() {
		$tokens = array();
		for ( $i = 0; $i < 10; $i++ ) {
			$value = 0 === $i ? '#000000' : 'var(--ds-color-missing)';
			for ( $j = 0; $j < 3; $j++ ) {
				$value = 'var(--ds-color-' . ( $i + 1 ) . ", {$value})";
			}
			$tokens[ "--ds-color-{$i}" ] = $value;
		}

		$colors = BLT_Design_Tokens::colors( $tokens );

		$this->assertCount( 10, $colors );
		$this->assertNull( $colors['color-0'] );
	}

	/**
	 * An overlong value is not measured.
	 */
	public function test_overlong_value_is_skipped() {
		$colors = BLT_Design_Tokens::colors( array( '--ds-color-long' => str_repeat( ' ', 600 ) . '#fff' ) );

		$this->assertNull( $colors['color-long'] );
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
				'--DS-color-upper'    => '#444444',
				"--ds-color-nl\n"     => '#555555',
				'--ds-color-ink'      => 'var(--ds-system-ink)',
			)
		);

		$this->assertSame( array( 'color-ink' => array( 17.0, 17.0, 17.0 ) ), $colors );
	}

	/**
	 * A post's stored tokens are read through the public call.
	 */
	public function test_reads_tokens() {
		DesignSystemPostType::$tokens = array( '--ds-color-text' => '#111' );

		$this->assertSame( array( '--ds-color-text' => '#111' ), BLT_Design_Tokens::tokens( self::factory()->post->create_and_get() ) );
	}
}
