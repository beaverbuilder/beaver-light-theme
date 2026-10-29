<?php
/**
 * Theme setup: supports, menus, content width.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and nav menu locations.
 *
 * The `header` and `footer` location slugs match bb-theme's so menus stay
 * assigned when a site switches over.
 *
 * @since 0.1.0
 * @return void
 */
function blt_setup(): void {
	load_theme_textdomain( 'beaver-light-theme', BLT_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 360,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'woocommerce' );

	register_nav_menus(
		array(
			'header' => __( 'Header Menu', 'beaver-light-theme' ),
			'footer' => __( 'Footer Menu', 'beaver-light-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'blt_setup' );

/**
 * Legacy content width for embeds; theme.json carries the real layout sizes.
 *
 * @since 0.1.0
 * @return void
 */
function blt_content_width(): void {
	$GLOBALS['content_width'] = 720;
}
add_action( 'after_setup_theme', 'blt_content_width', 0 );

/**
 * Enqueue the comment-reply script where threaded comments can appear.
 *
 * @since 0.1.0
 * @return void
 */
function blt_enqueue_comment_reply(): void {
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'blt_enqueue_comment_reply' );
