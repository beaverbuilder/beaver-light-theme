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
					'muted'   => 'color-night-raised',
					'line'    => 'color-haze',
				),
			),
			'no contrast'    => array( 'low-contrast', array() ),
			'all name-known' => array( 'role-named-status', array() ),
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
