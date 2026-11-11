<?php
/**
 * WordPress test suite configuration, read from environment variables.
 *
 * Every value has a default for a local MySQL with a throwaway database. The
 * suite drops and recreates tables in WP_TESTS_DB_NAME, so never point it at a
 * database that holds data.
 *
 * @package Beaver_Light_Theme
 */

/**
 * Read an environment variable, falling back to a default.
 *
 * @param string $name    Variable name.
 * @param string $default Value when the variable is unset.
 * @return string
 */
function blt_tests_env( string $name, string $default ): string {
	$value = getenv( $name );
	return false === $value ? $default : $value;
}

define( 'ABSPATH', rtrim( blt_tests_env( 'WP_CORE_DIR', sys_get_temp_dir() . '/wordpress' ), '/' ) . '/' );
define( 'DB_NAME', blt_tests_env( 'WP_TESTS_DB_NAME', 'wordpress_test' ) );
define( 'DB_USER', blt_tests_env( 'WP_TESTS_DB_USER', 'root' ) );
define( 'DB_PASSWORD', blt_tests_env( 'WP_TESTS_DB_PASSWORD', '' ) );
define( 'DB_HOST', blt_tests_env( 'WP_TESTS_DB_HOST', '127.0.0.1' ) );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Beaver Light Theme Tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
define( 'WP_DEBUG', true );
