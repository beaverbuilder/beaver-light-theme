<?php
/**
 * PHPUnit bootstrap: load the WordPress test library with this theme active.
 *
 * @package Beaver_Light
 */

$beaver_light_theme_dir = dirname( __DIR__ );
$beaver_light_wp_tests  = $beaver_light_theme_dir . '/vendor/wp-phpunit/wp-phpunit';

if ( false === getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );
}

require_once $beaver_light_theme_dir . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
require_once $beaver_light_wp_tests . '/includes/functions.php';

// The repo folder is the theme, so register its parent as a theme root and
// switch to it before WordPress loads the active theme's functions.php.
tests_add_filter(
	'setup_theme',
	static function () use ( $beaver_light_theme_dir ) {
		register_theme_directory( dirname( $beaver_light_theme_dir ) );
		switch_theme( basename( $beaver_light_theme_dir ) );
	}
);

require_once $beaver_light_wp_tests . '/includes/bootstrap.php';

// After WordPress, so the theme boots with no builder present, as on a bare site.
require_once __DIR__ . '/stubs/beaver-builder.php';
require_once __DIR__ . '/stubs/beaver-themer.php';
require_once __DIR__ . '/stubs/design-system.php';
require_once __DIR__ . '/class-beaver-light-test-case.php';
