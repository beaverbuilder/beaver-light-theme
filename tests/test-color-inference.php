<?php
/**
 * Hybrid color role matching: Beaver_Light_Color_Inference and Beaver_Light_Color_Rules.
 *
 * The fixtures are synthetic palettes shaped like the design kits (role names,
 * hue names, status colors, translucent tints) and like agent-written systems
 * with unfamiliar names, var() references and a dark ground.
 *
 * @package Beaver_Light
 */

/**
 * A role is matched by name, inferred with passing contrast, or left to fall through.
 */
class Test_Color_Inference extends Beaver_Light_Test_Case {

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
	 * Every role inference commits passes its contrast check, no name-matched role is overridden, and a measurable muted name left in passes text contrast.
	 *
	 * @dataProvider data_fixtures
	 *
	 * @param string $fixture Fixture name.
	 */
	public function test_committed_roles_pass_their_checks( string $fixture ) {
		$tokens   = self::fixture( $fixture );
		$colors   = Beaver_Light_Design_Tokens::colors( $tokens );
		$seeds    = Beaver_Light_Color_Inference::seeds( array_keys( $colors ) );
		$result   = Beaver_Light_Color_Inference::evaluate( $tokens );
		$inferred = $result['roles'];
		$roles    = $seeds + $inferred;
		$ratio    = static function ( string $a, string $b ) use ( $roles, $colors ): float {
			return Beaver_Light_Color::contrast( $colors[ $roles[ $a ] ], $colors[ $roles[ $b ] ] );
		};

		$this->assertSame( array(), array_intersect_key( $inferred, $seeds ) );

		if ( isset( $seeds['muted'], $colors[ $seeds['muted'] ] ) && ! in_array( $seeds['muted'], $result['rejected']['muted'] ?? array(), true ) ) {
			$this->assertGreaterThanOrEqual( 4.5, $ratio( 'muted', 'surface' ) );
		}

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
		$this->assertSame( $expected, Beaver_Light_Color_Inference::infer( self::fixture( $fixture ) ) );
	}

	/**
	 * Seeds take the first listed name present, for color roles only.
	 */
	public function test_seeds_follow_listed_order() {
		$seeds = Beaver_Light_Color_Inference::seeds( array( 'color-ink', 'color-heading', 'color-accent' ) );

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
		$this->assertSame( array(), Beaver_Light_Color_Inference::infer( $tokens ) );
	}

	/**
	 * A role whose check fails is absent while the rest commit.
	 */
	public function test_failed_role_is_absent() {
		$roles = Beaver_Light_Color_Inference::infer(
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

		$this->assertSame( array(), Beaver_Light_Color_Inference::infer( $tokens ) );
	}

	/**
	 * A known muted name below text contrast on the surface is rejected, and muted falls through when no color qualifies.
	 */
	public function test_faint_muted_name_is_rejected() {
		$result = Beaver_Light_Color_Inference::evaluate(
			array(
				'--ds-color-surface' => '#ffffff',
				'--ds-color-text'    => '#1f2328',
				'--ds-color-muted'   => '#e0e0e0',
			)
		);

		$this->assertSame( array( 'muted' => array( 'color-muted' ) ), $result['rejected'] );
		$this->assertArrayNotHasKey( 'muted', $result['roles'] );
	}

	/**
	 * A rejected muted name leaves the role open for a token picked by value.
	 */
	public function test_rejected_muted_is_inferred() {
		$result = Beaver_Light_Color_Inference::evaluate(
			array(
				'--ds-color-surface' => '#ffffff',
				'--ds-color-text'    => '#1f2328',
				'--ds-color-muted'   => '#e0e0e0',
				'--ds-color-slate'   => '#59606b',
			)
		);

		$this->assertSame( array( 'muted' => array( 'color-muted' ) ), $result['rejected'] );
		$this->assertSame( 'color-slate', $result['roles']['muted'] );
	}

	/**
	 * A legible muted name after a faint one stays the match, so nothing is inferred over it.
	 */
	public function test_next_legible_muted_name_stays_matched() {
		$result = Beaver_Light_Color_Inference::evaluate(
			array(
				'--ds-color-surface'    => '#ffffff',
				'--ds-color-text'       => '#1f2328',
				'--ds-color-text-muted' => '#e0e0e0',
				'--ds-color-muted'      => '#59606b',
				'--ds-color-slate'      => '#5f6670',
			)
		);

		$this->assertSame( array( 'muted' => array( 'color-text-muted' ) ), $result['rejected'] );
		$this->assertArrayNotHasKey( 'muted', $result['roles'] );
	}

	/**
	 * A legible or unmeasurable muted name is kept, and nothing is rejected when surface and text cannot be set.
	 *
	 * @return array
	 */
	public function data_muted_name_kept() {
		return array(
			'legible'      => array( array( '--ds-color-muted' => '#59606b' ) ),
			'unmeasurable' => array( array( '--ds-color-muted' => 'oklch(90% 0 0)' ) ),
			'no text pair' => array(
				array(
					'--ds-color-text'  => '#ffffff',
					'--ds-color-muted' => '#e0e0e0',
				),
			),
		);
	}

	/**
	 * Only a measurable known muted name that fails text contrast is rejected.
	 *
	 * @dataProvider data_muted_name_kept
	 *
	 * @param array $tokens Tokens added to a white surface and dark text.
	 */
	public function test_muted_name_kept( array $tokens ) {
		$result = Beaver_Light_Color_Inference::evaluate(
			$tokens + array(
				'--ds-color-surface' => '#ffffff',
				'--ds-color-text'    => '#1f2328',
			)
		);

		$this->assertSame( array(), $result['rejected'] );
		$this->assertArrayNotHasKey( 'muted', $result['roles'] );
	}

	/**
	 * A gray primary gets no hover shade, even when a color shares its nominal hue.
	 */
	public function test_gray_primary_gets_no_hover() {
		$roles = Beaver_Light_Color_Inference::infer(
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
		$roles = Beaver_Light_Color_Inference::infer(
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
		$roles = Beaver_Light_Color_Inference::infer( self::fixture( 'agent-plain' ) + array( '--ds-color-on-primary' => '#0b6e4f' ) );

		$this->assertArrayNotHasKey( 'primary', $roles );
		$this->assertArrayNotHasKey( 'primary-hover', $roles );
		$this->assertSame( 'color-charcoal', $roles['text'] );
	}
}
