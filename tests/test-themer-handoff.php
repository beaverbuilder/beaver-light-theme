<?php
/**
 * The Themer handoff: BLT_Themer::setup_headers_and_footers().
 *
 * @package Beaver_Light_Theme
 */

/**
 * The native header and footer give way only to Themer layouts that apply.
 */
class Test_Themer_Handoff extends BLT_Test_Case {

	/**
	 * The handoff runs on `wp`, once the query knows which layouts apply.
	 */
	public function test_handoff_is_hooked_on_wp() {
		$this->assertSame( 10, has_action( 'wp', array( 'BLT_Themer', 'setup_headers_and_footers' ) ) );
	}

	/**
	 * With no Themer layouts, the native header and footer stay.
	 */
	public function test_no_layouts_keeps_native_chrome() {
		BLT_Themer::setup_headers_and_footers();

		$this->assertSame( 10, has_action( 'blt_header', array( 'BLT_Chrome', 'render_header' ) ) );
		$this->assertSame( 10, has_action( 'blt_footer', array( 'BLT_Chrome', 'render_footer' ) ) );
		$this->assertFalse( has_action( 'blt_header', 'FLThemeBuilderLayoutRenderer::render_header' ) );
		$this->assertFalse( has_action( 'blt_footer', array( 'BLT_Themer', 'render_themer_footer' ) ) );
	}

	/**
	 * A Themer header replaces only the native header.
	 */
	public function test_header_layout_swaps_header_only() {
		FLThemeBuilderLayoutData::$header_ids = array( 12 );

		BLT_Themer::setup_headers_and_footers();

		$this->assertFalse( has_action( 'blt_header', array( 'BLT_Chrome', 'render_header' ) ) );
		$this->assertSame( 10, has_action( 'blt_header', 'FLThemeBuilderLayoutRenderer::render_header' ) );
		$this->assertSame( 10, has_action( 'blt_footer', array( 'BLT_Chrome', 'render_footer' ) ) );
	}

	/**
	 * A Themer footer replaces only the native footer.
	 */
	public function test_footer_layout_swaps_footer_only() {
		FLThemeBuilderLayoutData::$footer_ids = array( 34 );

		BLT_Themer::setup_headers_and_footers();

		$this->assertSame( 10, has_action( 'blt_header', array( 'BLT_Chrome', 'render_header' ) ) );
		$this->assertFalse( has_action( 'blt_footer', array( 'BLT_Chrome', 'render_footer' ) ) );
		$this->assertSame( 10, has_action( 'blt_footer', array( 'BLT_Themer', 'render_themer_footer' ) ) );
	}

	/**
	 * The swapped-in header renders through the blt_header position.
	 */
	public function test_themer_header_renders() {
		FLThemeBuilderLayoutData::$header_ids = array( 12 );
		BLT_Themer::setup_headers_and_footers();

		$this->assertSame( '[themer-header]', get_echo( 'do_action', array( 'blt_header' ) ) );
	}

	/**
	 * Footer open and close, and their fl_* aliases, still fire around a Themer footer.
	 */
	public function test_footer_positions_fire_around_themer_footer() {
		FLThemeBuilderLayoutData::$footer_ids = array( 34 );
		BLT_Themer::setup_headers_and_footers();

		// Priority 5 runs before the alias layer's priority-10 callback.
		$this->echo_on( 'blt_footer_open', '[open]', 5 );
		$this->echo_on( 'fl_before_footer_widgets', '[fl-open]' );
		$this->echo_on( 'blt_footer_close', '[close]', 5 );
		$this->echo_on( 'fl_after_footer_widgets', '[fl-close]' );

		$this->assertSame(
			'[open][fl-open][themer-footer][close][fl-close]',
			get_echo( 'do_action', array( 'blt_footer' ) )
		);
	}

	/**
	 * Print a marker when a hook fires.
	 *
	 * @param string $hook     Hook name.
	 * @param string $marker   Text to print.
	 * @param int    $priority Hook priority.
	 * @return void
	 */
	private function echo_on( string $hook, string $marker, int $priority = 10 ): void {
		add_action(
			$hook,
			static function () use ( $marker ) {
				echo $marker; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			},
			$priority
		);
	}
}
