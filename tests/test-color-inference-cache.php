<?php
/**
 * The per-revision cache of inferred color roles: BLT_Color_Inference::roles().
 *
 * @package Beaver_Light_Theme
 */

use FL\DesignSystem\DesignSystem\DesignSystemPostType;

/**
 * Roles are computed once per design system revision and stored in an option.
 */
class Test_Color_Inference_Cache extends BLT_Test_Case {

	/**
	 * A malformed cache is recomputed rather than trusted.
	 *
	 * @return array
	 */
	public function data_malformed_cache() {
		return array(
			'not an array'    => array( 'garbage' ),
			'no key'          => array( array( 'roles' => array() ) ),
			'roles not array' => array( array( 'key' => null, 'roles' => 'x' ) ),
		);
	}

	/**
	 * A malformed stored option is replaced with fresh roles.
	 *
	 * @dataProvider data_malformed_cache
	 *
	 * @param mixed $stored Stored option value; a null key is replaced with the current key.
	 */
	public function test_malformed_cache_recomputes( $stored ) {
		$post = self::factory()->post->create_and_get();
		self::set_default_design_system( $post );
		DesignSystemPostType::$tokens = self::fixture( 'agent-plain' );
		if ( is_array( $stored ) && array_key_exists( 'key', $stored ) ) {
			$stored['key'] = BLT_Color_Inference::cache_key( $post );
		}
		update_option( 'blt_color_roles', $stored );

		$this->assertSame( 'color-canvas', BLT_Color_Inference::roles()['surface'] );
	}

	/**
	 * Roles are cached per revision: the same key reuses them, a newer modified time recomputes.
	 */
	public function test_cache_follows_revision() {
		$post = self::factory()->post->create_and_get();
		self::set_default_design_system( $post );
		DesignSystemPostType::$tokens = self::fixture( 'agent-plain' );

		$first = BLT_Color_Inference::roles();
		$this->assertSame( 'color-canvas', $first['surface'] );

		DesignSystemPostType::$tokens = self::fixture( 'agent-references' );
		self::clear_request_cache( 'BLT_Color_Inference', 'roles' );
		$this->assertSame( $first, BLT_Color_Inference::roles() );

		$post->post_modified_gmt = '2030-01-01 00:00:00';
		self::clear_request_cache( 'BLT_Color_Inference', 'roles' );
		$this->assertSame( 'color-gray-0', BLT_Color_Inference::roles()['surface'] );
		$this->assertSame( BLT_Color_Inference::roles(), get_option( 'blt_color_roles' )['roles'] );
	}

	/**
	 * With no site default nothing is inferred and nothing is written.
	 */
	public function test_no_default_writes_nothing() {
		$this->assertSame( array(), BLT_Color_Inference::roles() );
		$this->assertFalse( get_option( 'blt_color_roles' ) );
	}
}
