<?php
/**
 * The site default design system: Beaver_Light_Design_System.
 *
 * @package Beaver_Light
 */

/**
 * One signal, the default post, decides whether a site default exists.
 */
class Test_Design_System extends Beaver_Light_Test_Case {

	/**
	 * With the option still set but its post gone, there is no site default and site-wide loading stays as asked.
	 */
	public function test_no_default_post() {
		update_option( 'fl_ds_site_default', 'a-uuid-whose-post-is-gone' );

		$this->assertNull( Beaver_Light_Design_System::default_post() );
		$this->assertFalse( Beaver_Light_Design_System::has_site_default() );
		$this->assertFalse( Beaver_Light_Design_System::load_site_wide( false ) );
	}

	/**
	 * With the option resolving to a post, that post is the site default and loads site-wide.
	 */
	public function test_default_post() {
		$post = self::factory()->post->create_and_get();
		self::set_default_design_system( $post );

		$this->assertSame( $post, Beaver_Light_Design_System::default_post() );
		$this->assertTrue( Beaver_Light_Design_System::has_site_default() );
		$this->assertTrue( Beaver_Light_Design_System::load_site_wide( false ) );
	}
}
