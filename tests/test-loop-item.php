<?php
/**
 * The archive loop item.
 *
 * @package Beaver_Light
 */

/**
 * A sticky post on the blog home carries a label for screen readers.
 */
class Test_Loop_Item extends WP_UnitTestCase {

	/**
	 * Render the loop item for one post from the main query.
	 *
	 * @param int $post_id Post to render.
	 * @return string
	 */
	private function render_loop_item( $post_id ) {
		global $wp_query;

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			if ( get_the_ID() !== $post_id ) {
				continue;
			}
			ob_start();
			try {
				require BEAVER_LIGHT_DIR . '/parts/loop-item.php';
			} finally {
				$output = ob_get_clean();
			}
			return $output;
		}

		$this->fail( 'Post not in the main query.' );
	}

	/**
	 * A sticky post on the blog home is labeled as featured.
	 */
	public function test_sticky_post_on_home_is_labeled() {
		$post_id = self::factory()->post->create();
		stick_post( $post_id );
		$this->go_to( home_url( '/' ) );

		$this->assertStringContainsString( 'Featured:', $this->render_loop_item( $post_id ) );
	}

	/**
	 * A post that is not sticky carries no label.
	 */
	public function test_regular_post_is_not_labeled() {
		$post_id = self::factory()->post->create();
		$this->go_to( home_url( '/' ) );

		$this->assertStringNotContainsString( 'Featured:', $this->render_loop_item( $post_id ) );
	}

	/**
	 * Outside the blog home a sticky post has no sticky border, so it carries no label.
	 */
	public function test_sticky_post_in_archive_is_not_labeled() {
		$category_id = self::factory()->category->create();
		$post_id     = self::factory()->post->create( array( 'post_category' => array( $category_id ) ) );
		stick_post( $post_id );
		$this->go_to( get_category_link( $category_id ) );

		$this->assertStringNotContainsString( 'Featured:', $this->render_loop_item( $post_id ) );
	}
}
