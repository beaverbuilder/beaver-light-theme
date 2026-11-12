<?php
/**
 * Check that a theme directory's version and requirement metadata agree.
 *
 * Consistency only: the X.X.X version format is Theme Check's rule, not this
 * script's. Exits 1 and lists every mismatch it finds.
 *
 * Usage: php check-metadata.php <theme-dir> [--tag=v1.2.3]
 *
 * @package Beaver_Light
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

/**
 * Read one header value from a file's top comment, or null when absent.
 *
 * @param string $contents File contents.
 * @param string $label    Header label, such as "Stable tag".
 * @return string|null
 */
function beaver_light_meta_header( string $contents, string $label ): ?string {
	$pattern = '/^[ \t\/*#@]*' . preg_quote( $label, '/' ) . ':[ \t]*(.*?)[ \t]*$/mi';
	return preg_match( $pattern, $contents, $match ) ? $match[1] : null;
}

/**
 * Collect the values of every `define( '*_VERSION', '...' )` in functions.php.
 *
 * @param string $contents functions.php contents.
 * @return string[]
 */
function beaver_light_meta_version_constants( string $contents ): array {
	$pattern = '/define\(\s*[\'"][A-Z0-9_]*_VERSION[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\s*\)/';
	preg_match_all( $pattern, $contents, $matches );
	return $matches[1];
}

/**
 * Compare the same field across style.css and readme.txt.
 *
 * @param string   $field  Field name for the message.
 * @param string[] $values Values keyed by where they were read.
 * @return string[] Error messages, empty when the values agree.
 */
function beaver_light_meta_compare( string $field, array $values ): array {
	if ( count( array_unique( $values ) ) <= 1 ) {
		return array();
	}
	$parts = array();
	foreach ( $values as $where => $value ) {
		$parts[] = $where . ' = "' . $value . '"';
	}
	return array( $field . ' differs: ' . implode( ', ', $parts ) );
}

/**
 * Check Version, Stable tag and the functions.php constant agree.
 *
 * @param string $style  style.css contents.
 * @param string $readme readme.txt contents.
 * @param string $funcs  functions.php contents.
 * @return string[] Error messages.
 */
function beaver_light_meta_version_errors( string $style, string $readme, string $funcs ): array {
	$consts = beaver_light_meta_version_constants( $funcs );
	$errors = array();

	if ( 1 !== count( $consts ) ) {
		$errors[] = 'functions.php must define exactly one *_VERSION constant, found ' . count( $consts );
	}
	return array_merge(
		$errors,
		beaver_light_meta_compare(
			'Version',
			array(
				'style.css Version'     => (string) beaver_light_meta_header( $style, 'Version' ),
				'readme.txt Stable tag' => (string) beaver_light_meta_header( $readme, 'Stable tag' ),
				'functions.php'         => (string) ( $consts[0] ?? '' ),
			)
		)
	);
}

/**
 * Check the minimum versions and tested-up-to match between style.css and readme.txt.
 *
 * @param string $style  style.css contents.
 * @param string $readme readme.txt contents.
 * @return string[] Error messages.
 */
function beaver_light_meta_requirement_errors( string $style, string $readme ): array {
	$errors = array();
	foreach ( array( 'Requires at least', 'Requires PHP', 'Tested up to' ) as $label ) {
		$errors = array_merge(
			$errors,
			beaver_light_meta_compare(
				$label,
				array(
					'style.css'  => (string) beaver_light_meta_header( $style, $label ),
					'readme.txt' => (string) beaver_light_meta_header( $readme, $label ),
				)
			)
		);
	}
	return $errors;
}

/**
 * Run every check against a theme directory.
 *
 * @param string      $dir Theme directory.
 * @param string|null $tag Release tag to match against Version, or null.
 * @return string[] Error messages.
 */
function beaver_light_meta_check( string $dir, ?string $tag ): array {
	$style   = (string) file_get_contents( $dir . '/style.css' );
	$readme  = (string) file_get_contents( $dir . '/readme.txt' );
	$funcs   = (string) file_get_contents( $dir . '/functions.php' );
	$version = (string) beaver_light_meta_header( $style, 'Version' );
	$domain  = (string) beaver_light_meta_header( $style, 'Text Domain' );

	$errors = array_merge(
		beaver_light_meta_version_errors( $style, $readme, $funcs ),
		beaver_light_meta_requirement_errors( $style, $readme )
	);
	if ( basename( $dir ) !== $domain ) {
		$errors[] = 'directory name "' . basename( $dir ) . '" differs from Text Domain "' . $domain . '"';
	}
	if ( null !== $tag && ltrim( $tag, 'v' ) !== $version ) {
		$errors[] = 'tag "' . $tag . '" differs from style.css Version "' . $version . '"';
	}
	return $errors;
}

$beaver_light_dir = null;
$beaver_light_tag = null;
foreach ( array_slice( $argv, 1 ) as $beaver_light_arg ) {
	if ( 0 === strpos( $beaver_light_arg, '--tag=' ) ) {
		$beaver_light_tag = substr( $beaver_light_arg, 6 );
	} else {
		$beaver_light_dir = rtrim( $beaver_light_arg, '/' );
	}
}
if ( null === $beaver_light_dir || ! is_dir( $beaver_light_dir ) ) {
	fwrite( STDERR, "Usage: php check-metadata.php <theme-dir> [--tag=v1.2.3]\n" );
	exit( 2 );
}

$beaver_light_errors = beaver_light_meta_check( $beaver_light_dir, $beaver_light_tag );
foreach ( $beaver_light_errors as $beaver_light_error ) {
	fwrite( STDERR, 'check-metadata: ' . $beaver_light_error . "\n" );
}
if ( $beaver_light_errors ) {
	exit( 1 );
}
echo "check-metadata: ok\n";
