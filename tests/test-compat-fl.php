<?php
/**
 * The frozen fl_* aliases: Beaver_Light_Compat_FL.
 *
 * @package Beaver_Light
 */

/**
 * Themer Parts saved against bb-theme keep rendering at the same positions.
 */
class Test_Compat_FL extends Beaver_Light_Test_Case {

	/**
	 * The frozen set, written out so a rename or removal fails here (CONTRIBUTING.md, Public surface).
	 *
	 * @return array canonical beaver_light_* hook => legacy fl_* alias
	 */
	private static function frozen_map(): array {
		return array(
			'beaver_light_page_open'            => 'fl_page_open',
			'beaver_light_page_close'           => 'fl_page_close',
			'beaver_light_header_before'        => 'fl_before_header',
			'beaver_light_header_after'         => 'fl_after_header',
			'beaver_light_content_before'       => 'fl_before_content',
			'beaver_light_content_open'         => 'fl_content_open',
			'beaver_light_content_close'        => 'fl_content_close',
			'beaver_light_content_after'        => 'fl_after_content',
			'beaver_light_footer_before'        => 'fl_before_footer',
			'beaver_light_footer_open'          => 'fl_before_footer_widgets',
			'beaver_light_footer_close'         => 'fl_after_footer_widgets',
			'beaver_light_footer_after'         => 'fl_after_footer',
			'beaver_light_entry_before'         => 'fl_before_post',
			'beaver_light_entry_after'          => 'fl_after_post',
			'beaver_light_entry_content_before' => 'fl_before_post_content',
			'beaver_light_entry_content_after'  => 'fl_after_post_content',
			'beaver_light_entry_meta_open'      => 'fl_post_top_meta_open',
			'beaver_light_entry_meta_close'     => 'fl_post_top_meta_close',
			'beaver_light_entry_footer_open'    => 'fl_post_bottom_meta_open',
			'beaver_light_entry_footer_close'   => 'fl_post_bottom_meta_close',
			'beaver_light_comments_open'        => 'fl_comments_open',
			'beaver_light_comments_close'       => 'fl_comments_close',
		);
	}

	/**
	 * Each canonical position paired with its alias.
	 *
	 * @return array
	 */
	public function data_aliases() {
		$cases = array();

		foreach ( self::frozen_map() as $canonical => $alias ) {
			$cases[ $alias ] = array( $canonical, $alias );
		}

		return $cases;
	}

	/**
	 * The alias map is exactly the frozen set; order does not matter.
	 */
	public function test_map_is_frozen() {
		$expected = self::frozen_map();
		$actual   = Beaver_Light_Compat_FL::MAP;
		ksort( $expected );
		ksort( $actual );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Every aliased position is a canonical hook the templates fire.
	 */
	public function test_every_alias_targets_a_canonical_hook() {
		$this->assertSame( array(), array_diff( array_keys( Beaver_Light_Compat_FL::MAP ), Beaver_Light_Hooks::names() ) );
	}

	/**
	 * Firing a canonical position fires its alias once, while that position is running.
	 *
	 * @dataProvider data_aliases
	 *
	 * @param string $canonical Canonical hook.
	 * @param string $alias     Legacy alias.
	 */
	public function test_alias_fires_at_its_position( string $canonical, string $alias ) {
		$inside = null;
		$before = did_action( $alias );

		add_action(
			$alias,
			static function () use ( &$inside, $canonical ) {
				$inside = doing_action( $canonical );
			}
		);

		Beaver_Light_Hooks::fire( $canonical );

		$this->assertTrue( $inside );
		$this->assertSame( 1, did_action( $alias ) - $before );
	}
}
