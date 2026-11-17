<?php
/**
 * Known muted names held to text contrast: Beaver_Light_Color_Inference::evaluate().
 *
 * Some design systems give --ds-color-muted to a background tone, so a known
 * muted name too faint on the surface is rejected rather than trusted.
 *
 * @package Beaver_Light
 */

/**
 * A faint known muted name is rejected; a legible or unmeasurable one is kept.
 */
class Test_Color_Inference_Muted extends Beaver_Light_Test_Case {

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

		$this->assertSame( array( 'color-muted' ), $result['rejected'] );
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

		$this->assertSame( array( 'color-muted' ), $result['rejected'] );
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

		$this->assertSame( array( 'color-text-muted' ), $result['rejected'] );
		$this->assertArrayNotHasKey( 'muted', $result['roles'] );
	}

	/**
	 * A legible or unmeasurable muted name is kept, and nothing is rejected on a surface that cannot be measured.
	 *
	 * @return array
	 */
	public function data_muted_name_kept() {
		return array(
			'legible'              => array( array( '--ds-color-muted' => '#59606b' ) ),
			'unmeasurable'         => array( array( '--ds-color-muted' => 'oklch(90% 0 0)' ) ),
			'unmeasurable surface' => array(
				array(
					'--ds-color-surface' => 'oklch(98% 0 0)',
					'--ds-color-muted'   => '#e0e0e0',
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
	 * A named surface still rejects a faint muted name when no text color passes, so nothing else is inferred.
	 */
	public function test_named_surface_rejects_without_text() {
		$result = Beaver_Light_Color_Inference::evaluate(
			array(
				'--ds-color-surface' => '#ffffff',
				'--ds-color-muted'   => '#e0e0e0',
				'--ds-color-accent'  => '#f59e0b',
			)
		);

		$this->assertSame( array( 'color-muted' ), $result['rejected'] );
		$this->assertSame( array(), $result['roles'] );
	}

	/**
	 * Both known muted names are rejected when both are faint.
	 */
	public function test_both_faint_muted_names_are_rejected() {
		$result = Beaver_Light_Color_Inference::evaluate(
			array(
				'--ds-color-surface'    => '#ffffff',
				'--ds-color-text'       => '#1f2328',
				'--ds-color-text-muted' => '#d0d0d0',
				'--ds-color-muted'      => '#e0e0e0',
			)
		);

		$this->assertSame( array( 'color-text-muted', 'color-muted' ), $result['rejected'] );
	}
}
