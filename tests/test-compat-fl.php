<?php
/**
 * The frozen fl_* aliases: BLT_Compat_FL.
 *
 * @package Beaver_Light_Theme
 */

/**
 * Themer Parts saved against bb-theme keep rendering at the same positions.
 */
class Test_Compat_FL extends BLT_Test_Case {

	/**
	 * The frozen set, written out so a rename or removal fails here (CONTRIBUTING.md, Public surface).
	 *
	 * @return array canonical blt_* hook => legacy fl_* alias
	 */
	private static function frozen_map(): array {
		return array(
			'blt_page_open'            => 'fl_page_open',
			'blt_page_close'           => 'fl_page_close',
			'blt_header_before'        => 'fl_before_header',
			'blt_header_after'         => 'fl_after_header',
			'blt_content_before'       => 'fl_before_content',
			'blt_content_open'         => 'fl_content_open',
			'blt_content_close'        => 'fl_content_close',
			'blt_content_after'        => 'fl_after_content',
			'blt_footer_before'        => 'fl_before_footer',
			'blt_footer_open'          => 'fl_before_footer_widgets',
			'blt_footer_close'         => 'fl_after_footer_widgets',
			'blt_footer_after'         => 'fl_after_footer',
			'blt_entry_before'         => 'fl_before_post',
			'blt_entry_after'          => 'fl_after_post',
			'blt_entry_content_before' => 'fl_before_post_content',
			'blt_entry_content_after'  => 'fl_after_post_content',
			'blt_entry_meta_open'      => 'fl_post_top_meta_open',
			'blt_entry_meta_close'     => 'fl_post_top_meta_close',
			'blt_entry_footer_open'    => 'fl_post_bottom_meta_open',
			'blt_entry_footer_close'   => 'fl_post_bottom_meta_close',
			'blt_comments_open'        => 'fl_comments_open',
			'blt_comments_close'       => 'fl_comments_close',
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
	 * The alias map is exactly the frozen set.
	 */
	public function test_map_is_frozen() {
		$this->assertSame( self::frozen_map(), BLT_Compat_FL::MAP );
	}

	/**
	 * Every aliased position is a canonical hook the templates fire.
	 */
	public function test_every_alias_targets_a_canonical_hook() {
		$this->assertSame( array(), array_diff( array_keys( BLT_Compat_FL::MAP ), BLT_Hooks::names() ) );
	}

	/**
	 * Firing a canonical position fires its alias, while that position is running.
	 *
	 * @dataProvider data_aliases
	 *
	 * @param string $canonical Canonical hook.
	 * @param string $alias     Legacy alias.
	 */
	public function test_alias_fires_at_its_position( string $canonical, string $alias ) {
		$inside = null;

		add_action(
			$alias,
			static function () use ( &$inside, $canonical ) {
				$inside = doing_action( $canonical );
			}
		);

		BLT_Hooks::fire( $canonical );

		$this->assertTrue( $inside );
	}
}
