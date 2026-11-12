<?php
/**
 * Hybrid color role matching: BLT_Color_Inference and BLT_Color_Rules.
 *
 * The fixtures are synthetic palettes shaped like the design kits (role names,
 * hue names, status colors, translucent tints) and like agent-written systems
 * with unfamiliar names, var() references and a dark ground.
 *
 * @package Beaver_Light_Theme
 */

use FL\DesignSystem\DesignSystem\DesignSystemPostType;

/**
 * A role is matched by name, inferred with passing contrast, or left to fall through.
 */
class Test_Color_Inference extends BLT_Test_Case {

	/**
	 * Every fixture palette.
	 *
	 * @return array
	 */
	public function data_fixtures() {
		$fixtures = array();

		foreach ( glob( __DIR__ . '/fixtures/kits/*.json' ) as $file ) {
			$fixtures[ basename( $file, '.json' ) ] = array( basename( $file, '.json' ) );
		}

		return $fixtures;
	}

	/**
	 * Every role inference commits passes its contrast check, and no name-matched role is overridden.
	 *
	 * @dataProvider data_fixtures
	 *
	 * @param string $fixture Fixture name.
	 */
	public function test_committed_roles_pass_their_checks( string $fixture ) {
		$tokens   = self::fixture( $fixture );
		$colors   = BLT_Design_Tokens::colors( $tokens );
		$seeds    = BLT_Color_Inference::seeds( array_keys( $colors ) );
		$inferred = BLT_Color_Inference::infer( $tokens );
		$roles    = $seeds + $inferred;
		$ratio    = static function ( string $a, string $b ) use ( $roles, $colors ): float {
			return BLT_Color::contrast( $colors[ $roles[ $a ] ], $colors[ $roles[ $b ] ] );
		};

		$this->assertSame( array(), array_intersect_key( $inferred, $seeds ) );

		if ( array() === $inferred ) {
			return;
		}

		$this->assertGreaterThanOrEqual( 4.5, $ratio( 'text', 'surface' ) );

		foreach ( array( 'muted', 'link', 'link-hover' ) as $role ) {
			if ( isset( $inferred[ $role ] ) ) {
				$this->assertGreaterThanOrEqual( 4.5, $ratio( $role, 'surface' ), $role );
			}
		}

		if ( isset( $inferred['line'] ) ) {
			$this->assertGreaterThanOrEqual( 1.25, $ratio( 'line', 'surface' ) );
			$this->assertLessThanOrEqual( 3.0, $ratio( 'line', 'surface' ) );
		}

		if ( isset( $inferred['primary'] ) || isset( $inferred['on-primary'] ) ) {
			$this->assertGreaterThanOrEqual( 3.0, $ratio( 'primary', 'surface' ) );
			$this->assertGreaterThanOrEqual( 4.5, $ratio( 'on-primary', 'primary' ) );
		}

		if ( isset( $inferred['primary-hover'] ) ) {
			$this->assertGreaterThanOrEqual( 4.5, $ratio( 'on-primary', 'primary-hover' ) );
		}
	}

	/**
	 * The exact roles picked for agent-style systems, whose names no list could anticipate.
	 *
	 * @return array
	 */
	public function data_pinned() {
		return array(
			'plain names'    => array(
				'agent-plain',
				array(
					'surface'       => 'color-canvas',
					'text'          => 'color-charcoal',
					'heading'       => 'color-charcoal',
					'muted'         => 'color-slate',
					'line'          => 'color-mist',
					'primary'       => 'color-brand',
					'on-primary'    => 'color-canvas',
					'primary-hover' => 'color-brand-strong',
					'link'          => 'color-brand',
					'link-hover'    => 'color-brand-strong',
				),
			),
			'var() aliases'  => array(
				'agent-references',
				array(
					'surface'       => 'color-gray-0',
					'text'          => 'color-gray-900',
					'heading'       => 'color-gray-900',
					'muted'         => 'color-gray-600',
					'line'          => 'color-gray-300',
					'primary'       => 'color-blue-600',
					'on-primary'    => 'color-gray-0',
					'primary-hover' => 'color-blue-800',
					'link'          => 'color-blue-600',
					'link-hover'    => 'color-blue-800',
				),
			),
			// The known limitation: a dark-ground system is inferred as a light page.
			'dark ground'    => array(
				'agent-dark',
				array(
					'surface' => 'color-starlight',
					'text'    => 'color-night',
					'heading' => 'color-night',
					'line'    => 'color-haze',
				),
			),
			// Text is named; brick outranks pine on chroma, and pine-light has no readable on-primary.
			'hue names'      => array(
				'hue-named',
				array(
					'surface'    => 'color-white',
					'muted'      => 'color-text-light',
					'primary'    => 'color-brick',
					'on-primary' => 'color-white',
					'link'       => 'color-brick',
				),
			),
			// The near-black plums pass HSL saturation but not chroma, and brass fails as a fill on white.
			'deep hues'      => array(
				'hue-named-deep',
				array(
					'surface' => 'color-white',
					'text'    => 'color-plum',
					'heading' => 'color-plum',
				),
			),
			'no contrast'    => array( 'low-contrast', array() ),
			'all name-known' => array( 'role-named-status', array() ),
			'mostly named'   => array( 'role-named', array() ),
		);
	}

	/**
	 * Agent-style systems get these exact roles.
	 *
	 * @dataProvider data_pinned
	 *
	 * @param string $fixture  Fixture name.
	 * @param array  $expected Role => token name.
	 */
	public function test_pinned_results( string $fixture, array $expected ) {
		$this->assertSame( $expected, BLT_Color_Inference::infer( self::fixture( $fixture ) ) );
	}

	/**
	 * Seeds take the first listed name present, for color roles only.
	 */
	public function test_seeds_follow_listed_order() {
		$seeds = BLT_Color_Inference::seeds( array( 'color-ink', 'color-heading', 'color-accent' ) );

		$this->assertSame(
			array(
				'heading' => 'color-heading',
				'primary' => 'color-accent',
				'link'    => 'color-accent',
			),
			$seeds
		);
	}

	/**
	 * Inferred roles are measured against name-matched ones, so a seeded surface decides the text check.
	 */
	public function test_inference_measures_against_seeds() {
		$tokens = array(
			'--ds-color-surface' => '#222222',
			'--ds-color-white'   => '#ffffff',
			'--ds-color-black'   => '#000000',
		);

		// The darkest color fails on the seeded dark surface, so nothing is inferred.
		$this->assertSame( array(), BLT_Color_Inference::infer( $tokens ) );
	}

	/**
	 * A role whose check fails is absent while the rest commit.
	 */
	public function test_failed_role_is_absent() {
		$roles = BLT_Color_Inference::infer(
			array(
				'--ds-color-paper' => '#ffffff',
				'--ds-color-ink'   => '#111111',
				'--ds-color-lemon' => '#fff44f',
			)
		);

		$this->assertSame( 'color-paper', $roles['surface'] );
		$this->assertArrayNotHasKey( 'primary', $roles );
		$this->assertArrayNotHasKey( 'link', $roles );
	}

	/**
	 * A seeded surface that cannot be measured switches inference off rather than guessing around it.
	 */
	public function test_unmeasurable_seed_stops_inference() {
		$tokens = self::fixture( 'agent-plain' ) + array( '--ds-color-surface' => 'oklch(98% 0 0)' );

		$this->assertSame( array(), BLT_Color_Inference::infer( $tokens ) );
	}

	/**
	 * A gray primary gets no hover shade, even when a color shares its nominal hue.
	 */
	public function test_gray_primary_gets_no_hover() {
		$roles = BLT_Color_Inference::infer(
			array(
				'--ds-color-paper'   => '#ffffff',
				'--ds-color-ink'     => '#111111',
				'--ds-color-primary' => '#6b7280',
				'--ds-color-navy'    => '#1e3a8a',
			)
		);

		$this->assertArrayNotHasKey( 'primary-hover', $roles );
		$this->assertArrayNotHasKey( 'link-hover', $roles );
	}

	/**
	 * Link-hover never commits without a link, so a link cannot change color only on hover.
	 */
	public function test_link_hover_needs_link() {
		$roles = BLT_Color_Inference::infer(
			array(
				'--ds-color-surface' => '#ede6d6',
				'--ds-color-white'   => '#ffffff',
				'--ds-color-ink'     => '#111111',
				'--ds-color-brand'   => '#2f7a5a',
				'--ds-color-brand-2' => '#1f5a41',
			)
		);

		$this->assertSame( 'color-brand', $roles['primary'] );
		$this->assertSame( 'color-brand-2', $roles['primary-hover'] );
		$this->assertArrayNotHasKey( 'link', $roles );
		$this->assertArrayNotHasKey( 'link-hover', $roles );
	}

	/**
	 * A seeded on-primary that is unreadable on every fill keeps primary open.
	 */
	public function test_seeded_on_primary_must_be_readable() {
		$roles = BLT_Color_Inference::infer( self::fixture( 'agent-plain' ) + array( '--ds-color-on-primary' => '#0b6e4f' ) );

		$this->assertArrayNotHasKey( 'primary', $roles );
		$this->assertArrayNotHasKey( 'primary-hover', $roles );
		$this->assertSame( 'color-charcoal', $roles['text'] );
	}

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
		$post                          = self::factory()->post->create_and_get();
		DesignSystemPostType::$default = $post;
		DesignSystemPostType::$tokens  = self::fixture( 'agent-plain' );
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
		$post                          = self::factory()->post->create_and_get();
		DesignSystemPostType::$default = $post;
		DesignSystemPostType::$tokens  = self::fixture( 'agent-plain' );

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

	/**
	 * A fixture's token map.
	 *
	 * @param string $name Fixture name.
	 * @return array
	 */
	private static function fixture( string $name ): array {
		return json_decode( file_get_contents( __DIR__ . "/fixtures/kits/{$name}.json" ), true );
	}
}
