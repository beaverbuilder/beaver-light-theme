<?php
/**
 * Width parity: BLT_Styles::widths().
 *
 * @package Beaver_Light_Theme
 */

/**
 * The site width matches the builder's row width, and the gutter matches where text sits in a row.
 */
class Test_Styles_Widths extends BLT_Test_Case {

	/**
	 * Builder global settings and the widths they should produce.
	 *
	 * @return array
	 */
	public function data_widths() {
		$pad = 'row_padding_left';
		$mar = 'module_margins_left';

		return array(
			'nothing set'                 => array( array(), '1100px', '20px' ),
			'row width, default unit'     => array( array( 'row_width' => '1200' ), '1200px', '20px' ),
			'row width in percent'        => array( array( 'row_width' => '90', 'row_width_unit' => '%' ), '90%', '20px' ),
			'padding plus margin'         => array( array( $pad => '20', $mar => '10' ), '1100px', '30px' ),
			'padding plus margin in rem'  => array( array( $pad => '1.5', 'row_padding_unit' => 'rem', $mar => '0.5', 'module_margins_unit' => 'rem' ), '1100px', '2rem' ),
			'margin in a different unit'  => array( array( $pad => '2', 'row_padding_unit' => 'em', $mar => '10' ), '1100px', '2em' ),
			'padding without margin'      => array( array( $pad => '15' ), '1100px', '15px' ),
			'zero padding keeps margin'   => array( array( $pad => '0', $mar => '10' ), '1100px', '10px' ),
			'margin without padding'      => array( array( $mar => '10' ), '1100px', '20px' ),
			'empty units fall back to px' => array( array( 'row_width' => '1000', 'row_width_unit' => '', $pad => '12', 'row_padding_unit' => '', $mar => '6', 'module_margins_unit' => '' ), '1000px', '18px' ),
		);
	}

	/**
	 * Each settings combination resolves to the expected site width and gutter.
	 *
	 * @dataProvider data_widths
	 *
	 * @param array  $settings Builder global settings.
	 * @param string $site     Expected site width.
	 * @param string $gutter   Expected gutter.
	 */
	public function test_widths( array $settings, string $site, string $gutter ) {
		FLBuilderModel::$global_settings = (object) $settings;

		$this->assertSame(
			array(
				'site'   => $site,
				'gutter' => $gutter,
			),
			BLT_Styles::widths()
		);
	}
}
