<?php
/**
 * The No Header/Footer page template, part of the theme's public surface (CONTRIBUTING.md).
 *
 * @package Beaver_Light_Theme
 */

/**
 * The template renders through singular.php, preferring a child theme's copy.
 */
class Test_No_Header_Footer_Template extends WP_UnitTestCase {

	/**
	 * Stand-in child theme directory, removed after each test.
	 *
	 * @var string
	 */
	private $child_dir = '';

	/**
	 * Point the stylesheet directory at a temporary child theme with its own singular.php.
	 */
	private function use_child_singular() {
		$this->child_dir = get_temp_dir() . 'blt-child-' . wp_generate_password( 8, false );
		wp_mkdir_p( $this->child_dir );
		file_put_contents( $this->child_dir . '/singular.php', "<?php echo 'blt-child-singular';" );

		$child_dir = $this->child_dir;
		add_filter(
			'stylesheet_directory',
			static function () use ( $child_dir ) {
				return $child_dir;
			}
		);
		wp_set_template_globals();
	}

	/**
	 * Render the template file and return its output.
	 *
	 * @return string
	 */
	private function render_template() {
		ob_start();
		try {
			require BLT_DIR . '/tpl-no-header-footer.php';
		} finally {
			$output = ob_get_clean();
		}
		return $output;
	}

	/**
	 * Remove the child theme and restore the cached theme paths.
	 */
	public function tear_down() {
		if ( $this->child_dir && file_exists( $this->child_dir . '/singular.php' ) ) {
			unlink( $this->child_dir . '/singular.php' );
		}
		if ( $this->child_dir && is_dir( $this->child_dir ) ) {
			rmdir( $this->child_dir );
		}
		parent::tear_down();
		wp_set_template_globals();
	}

	/**
	 * A child theme's singular.php is loaded instead of this theme's.
	 */
	public function test_loads_child_theme_singular() {
		$this->use_child_singular();

		$this->assertSame( 'blt-child-singular', $this->render_template() );
	}

	/**
	 * Without a child theme, this theme's singular.php renders the post.
	 */
	public function test_loads_theme_singular_without_child() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'blt-parent-content' ) );
		$this->go_to( get_permalink( $post_id ) );

		$this->assertStringContainsString( 'blt-parent-content', $this->render_template() );
	}
}
