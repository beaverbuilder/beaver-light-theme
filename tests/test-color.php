<?php
/**
 * Color parsing and measurement: Beaver_Light_Color.
 *
 * @package Beaver_Light
 */

/**
 * Opaque sRGB formats parse; everything else is skipped, never guessed.
 */
class Test_Color extends Beaver_Light_Test_Case {

	/**
	 * Each accepted format and the channels it parses to.
	 *
	 * @return array
	 */
	public function data_parseable() {
		return array(
			'short hex'          => array( '#0f8', array( 0, 255, 136 ) ),
			'short hex, alpha'   => array( '#0f8f', array( 0, 255, 136 ) ),
			'hex'                => array( '#1D4ED8', array( 29, 78, 216 ) ),
			'hex, opaque alpha'  => array( '#1d4ed8ff', array( 29, 78, 216 ) ),
			'rgb, commas'        => array( 'rgb(1, 2, 3)', array( 1, 2, 3 ) ),
			'rgb, spaces'        => array( 'rgb(1 2 3)', array( 1, 2, 3 ) ),
			'rgb, percentages'   => array( 'rgb(100%, 0%, 50%)', array( 255, 0, 127.5 ) ),
			'rgba, opaque'       => array( 'rgba(1, 2, 3, 1)', array( 1, 2, 3 ) ),
			'rgb, slash alpha'   => array( 'rgb(1 2 3 / 100%)', array( 1, 2, 3 ) ),
			'hsl, commas'        => array( 'hsl(0, 100%, 50%)', array( 255, 0, 0 ) ),
			'hsl, spaces, deg'   => array( 'hsl(120deg 100% 25%)', array( 0, 127.5, 0 ) ),
			'hsla, opaque'       => array( 'hsla(240, 100%, 50%, 1)', array( 0, 0, 255 ) ),
			'padded, upper case' => array( '  RGB(10,20,30)  ', array( 10, 20, 30 ) ),
		);
	}

	/**
	 * An accepted format parses to its channels.
	 *
	 * @dataProvider data_parseable
	 *
	 * @param string $value    CSS color.
	 * @param array  $expected RGB channels.
	 */
	public function test_parses( string $value, array $expected ) {
		$rgb = Beaver_Light_Color::parse( $value );

		$this->assertNotNull( $rgb );
		foreach ( $expected as $i => $channel ) {
			$this->assertEqualsWithDelta( $channel, $rgb[ $i ], 0.01, $value );
		}
	}

	/**
	 * Values inference must skip.
	 *
	 * @return array
	 */
	public function data_unparseable() {
		return array(
			'translucent hex'   => array( '#00000080' ),
			'translucent short' => array( '#0008' ),
			'translucent rgba'  => array( 'rgba(1, 2, 3, 0.5)' ),
			'translucent slash' => array( 'rgb(1 2 3 / 50%)' ),
			'translucent hsla'  => array( 'hsla(0, 100%, 50%, 0.2)' ),
			'oklch'             => array( 'oklch(70% 0.1 250)' ),
			'color-mix'         => array( 'color-mix(in srgb, red, blue)' ),
			'named'             => array( 'rebeccapurple' ),
			'gradient'          => array( 'linear-gradient(#fff, #000)' ),
			'var'               => array( 'var(--ds-color-x)' ),
			'five hex digits'   => array( '#12345' ),
			'two channels'      => array( 'rgb(1, 2)' ),
			'turn hue'          => array( 'hsl(0.5turn, 50%, 50%)' ),
			'empty'             => array( '' ),
		);
	}

	/**
	 * Unparseable and translucent values return null.
	 *
	 * @dataProvider data_unparseable
	 *
	 * @param string $value CSS value.
	 */
	public function test_skips( string $value ) {
		$this->assertNull( Beaver_Light_Color::parse( $value ) );
	}

	/**
	 * Contrast follows WCAG: 21 for black on white, 1 for a color on itself, symmetric.
	 */
	public function test_contrast() {
		$white = Beaver_Light_Color::parse( '#fff' );
		$black = Beaver_Light_Color::parse( '#000' );
		$gray  = Beaver_Light_Color::parse( '#777' );

		$this->assertEqualsWithDelta( 21.0, Beaver_Light_Color::contrast( $black, $white ), 0.001 );
		$this->assertEqualsWithDelta( 1.0, Beaver_Light_Color::contrast( $gray, $gray ), 0.001 );
		$this->assertEqualsWithDelta( 4.48, Beaver_Light_Color::contrast( $white, $gray ), 0.01 );
		$this->assertSame( Beaver_Light_Color::contrast( $white, $gray ), Beaver_Light_Color::contrast( $gray, $white ) );
	}

	/**
	 * Saturation, chroma and hue of a few reference colors.
	 */
	public function test_saturation_chroma_and_hue() {
		$red  = Beaver_Light_Color::parse( '#ff0000' );
		$navy = Beaver_Light_Color::parse( '#000080' );
		$gray = Beaver_Light_Color::parse( '#808080' );

		$this->assertEqualsWithDelta( 1.0, Beaver_Light_Color::saturation( $red ), 0.001 );
		$this->assertEqualsWithDelta( 0.0, Beaver_Light_Color::saturation( $gray ), 0.001 );
		$this->assertEqualsWithDelta( 1.0, Beaver_Light_Color::chroma( $red ), 0.001 );
		$this->assertEqualsWithDelta( 0.502, Beaver_Light_Color::chroma( $navy ), 0.001 );
		$this->assertEqualsWithDelta( 0.0, Beaver_Light_Color::hue( $red ), 0.001 );
		$this->assertEqualsWithDelta( 240.0, Beaver_Light_Color::hue( $navy ), 0.001 );
		$this->assertEqualsWithDelta( 300.0, Beaver_Light_Color::hue( Beaver_Light_Color::parse( '#ff00ff' ) ), 0.001 );
	}
}
